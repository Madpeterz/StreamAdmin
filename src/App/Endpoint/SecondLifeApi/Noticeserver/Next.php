<?php

namespace App\Endpoint\SecondLifeApi\Noticeserver;

use App\Helpers\BotHelper;
use App\Helpers\EventsQHelper;
use App\Helpers\NoticesHelper;
use App\Helpers\SwapablesHelper;
use App\MediaServer\Logic\ApiLogicExpire;
use App\R7\Model\Apis;
use App\R7\Model\Avatar;
use App\R7\Model\Notecard;
use App\R7\Model\Notice;
use App\R7\Model\Noticenotecard;
use App\R7\Set\NoticeSet;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Rentalnoticeptout;
use App\R7\Set\RentalSet;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        global $unixtime_hour;
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "SystemAPI access only - please contact support");
            return;
        }

        $notice_set = new NoticeSet();
        $rental_set = new RentalSet();

        $notice_set->loadAll();
        $sorted_linked = $notice_set->getLinkedArray("hoursRemaining", "id");
        ksort($sorted_linked, SORT_NUMERIC);
        $max_hours = array_keys($sorted_linked)[count($sorted_linked) - 2]; // ignore 999 hours at the end for active
        $unixtime = $max_hours * $unixtime_hour;
        $expired_notice = $notice_set->getObjectByField("hoursRemaining", 0);

        $where_config = [
            "fields" => ["expireUnixtime","noticeLink"],
            "values" => [(time() + $unixtime),$expired_notice->getId()],
            "types" => ["i","i"],
            "matches" => ["<=","!="],
        ];

        $rental_set->loadWithConfig($where_config);
        if ($rental_set->getCount() == 0) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "nowork");
            return;
        }

        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "nowork");

        foreach ($rental_set->getAllIds() as $id) {
            $rental = $rental_set->getObjectByID($id);
            $stop = $this->continueProcess(
                $rental,
                $expired_notice,
                $notice_set,
                $sorted_linked
            );
            if ($stop == true) {
                break;
            }
        }
    }

    protected function continueProcess(
        Rental $rental,
        Notice $expired_notice,
        NoticeSet $notice_set,
        array $sorted_linked
    ): bool {
        global $unixtime_hour;
        $server = new Server();
        $apis = new Apis();
        $package = new Package();
        $stream = new Stream();
        $avatar = new Avatar();

        $avatar->loadID($rental->getAvatarLink());
        $stream->loadID($rental->getStreamLink());
        $server->loadID($stream->getServerLink());
        $package->loadID($stream->getPackageLink());
        $apis->loadID($server->getApiLink());

        if ($rental->getExpireUnixtime() < time()) {
            $this->processNoticeChange(
                $expired_notice,
                $rental,
                $package,
                $avatar,
                $stream,
                $server
            );
            return true;
        }

        $hours_remain = ceil(($rental->getExpireUnixtime() - time()) / $unixtime_hour);
        if ($hours_remain < 0) {
            $this->failed("Math error - negitive hours remaining but not expired");
            return false;
        }

        $current_notice_level = $notice_set->getObjectByID($rental->getNoticeLink());
        $current_hold_hours = $current_notice_level->getHoursRemaining();
        $use_notice_index = $sorted_linked[$current_hold_hours];
        foreach ($sorted_linked as $hours => $index) {
            if (($hours > 0) && ($hours < 999)) {
                if ($hours > $hours_remain) {
                    if ($hours < $current_hold_hours) {
                        $use_notice_index = $index;
                    }
                    break;
                }
            }
        }

        if ($use_notice_index == $current_notice_level->getId()) {
            return false;
        }

        $notice = $notice_set->getObjectByID($use_notice_index);
        $this->processNoticeChange(
            $notice,
            $rental,
            $package,
            $avatar,
            $stream,
            $server
        );
        return true;
    }

    protected function processNoticeChange(
        Notice $notice,
        Rental $rental,
        Package $package,
        Avatar $avatar,
        Stream $stream,
        Server $server
    ): void {
        $this->setSwapTag("message", "Processing notice change");
        $rentalNoticeOptout = new Rentalnoticeptout();
        $whereConfig = [
            "fields" => ["rentalLink","noticeLink"],
            "values" => [$rental->getId(),$notice->getId()],
        ];
        $rentalNoticeOptout->loadWithConfig($whereConfig);
        $skipNotice = $rentalNoticeOptout->isLoaded();

        if ($skipNotice == false) {
            $bot_helper = new BotHelper();
            $swapables_helper = new SwapablesHelper();
            $sendmessage = $swapables_helper->getSwappedText(
                $notice->getImMessage(),
                $avatar,
                $rental,
                $package,
                $server,
                $stream
            );
            $sendMessage_status = $bot_helper->sendMessage(
                $avatar,
                $sendmessage,
                $notice->getUseBot(),
                $notice->getSendObjectIM()
            );
            if ($sendMessage_status["status"] == false) {
                $this->failed("Unable to put mail into outbox");
                return;
            }
        }
        $rental->setNoticeLink($notice->getId());
        $save_status = $rental->updateEntry();
        if ($save_status["status"] == false) {
            $this->failed("Unable to update rental notice level");
            return;
        }

        if ($notice->getHoursRemaining() == 0) {
            $EventsQHelper = new EventsQHelper();
            $EventsQHelper->addToEventQ("RentalExpire", $package, $avatar, $server, $stream, $rental);

            $selectedApi = new Apis();
            $selectedApi->loadID($server->getApiLink());
            if ($selectedApi->isLoaded() == false) {
                $this->failed("Unable to load API");
                return;
            }
            if (
                ($selectedApi->getEventDisableExpire() == true) &&
                ($server->getEventDisableExpire() == true) &&
                ($package->getApiAllowAutoSuspend() == true) &&
                ($rental->getApiAllowAutoSuspend() == true)
            ) {
                if ($package->getApiAutoSuspendDelayHours() == 0) {
                    $rental->setApiSuspended(true);
                    $rental->setApiPendingAutoSuspend(false);
                    $rental->setApiPendingAutoSuspendAfter(time());
                    $apilogic = new ApiLogicExpire();
                    $apilogic->setStream($stream);
                    $apilogic->setServer($server);
                    $apilogic->setRental($rental);
                    $reply = $apilogic->createNextApiRequest();
                    if ($reply["status"] == false) {
                        $this->failed("API server logic has failed on ApiLogicExpire: " . $reply["message"]);
                        return;
                    }
                }
                if ($package->getApiAutoSuspendDelayHours() > 0) {
                    $rental->setApiSuspended(false);
                    $rental->setApiPendingAutoSuspend(true);
                    $rental->setApiPendingAutoSuspendAfter(time() +
                        ((60 * 60) * $package->getApiAutoSuspendDelayHours()));
                }
                $save_status = $rental->updateEntry();
                if ($save_status["status"] == false) {
                    $this->failed("Unable to update rental API auto suspend config");
                    return;
                }
            }
        }
        $this->ok("ok");
        if ($skipNotice == false) {
            if ($notice->getSendNotecard() == true) {
                if ($bot_helper->getNotecards() == true) {
                    $notecard = new Notecard();
                    $notecard->setRentalLink($rental->getId());
                    $notecard->setAsNotice(1);
                    $notecard->setNoticeLink($notice->getId());
                    $create_status = $notecard->createEntry();
                    if ($create_status["status"] == false) {
                        $this->failed("Unable to create new notecard");
                        return;
                    }
                }
            }
            if ($notice->getNoticeNotecardLink() <= 1) {
                return;
            }
            $notice_notecard = new Noticenotecard();
            if ($notice_notecard->loadID($notice->getNoticeNotecardLink()) == false) {
                $this->failed("Unable to find static notecard!");
                return;
            }
            if ($notice_notecard->getMissing() == false) {
                $this->setSwapTag("send_static_notecard", $notice_notecard->getName());
                $this->setSwapTag("send_static_notecard_to", $avatar->getAvatarUUID());
            }
        }

        return;
    }
}
