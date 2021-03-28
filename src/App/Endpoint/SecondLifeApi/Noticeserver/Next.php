<?php

namespace App\Endpoint\SecondLifeApi\Noticeserver;

use App\Helpers\BotHelper;
use App\Helpers\SwapablesHelper;
use App\MediaServer\Logic\ApiLogicExpire;
use App\R7\Model\Apis;
use App\R7\Model\Avatar;
use App\R7\Model\Botconfig;
use App\R7\Model\Notecard;
use App\R7\Model\Notice;
use App\R7\Model\Noticenotecard;
use App\R7\Set\NoticeSet;
use App\R7\Model\Package;
use App\R7\Model\Rental;
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

        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) == false) {
            $this->setSwapTag("message", "Unable to load bot config");
            return;
        }

        if ($botconfig->getAvatarLink() <= 0) {
            $this->setSwapTag("message", "Assigned avatar to bot is not vaild");
            return;
        }

        $botavatar = new Avatar();
        if ($botavatar->loadID($botconfig->getAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load avatar attached to bot");
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
                $botconfig,
                $sorted_linked,
                $botavatar
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
        Botconfig $botconfig,
        array $sorted_linked,
        Avatar $botavatar
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
                $server,
                $botconfig,
                $botavatar
            );
            return true;
        }

        $hours_remain = ceil(($rental->getExpireUnixtime() - time()) / $unixtime_hour);
        if ($hours_remain < 0) {
            $this->setSwapTag("message", "Math error - negitive hours remaining but not expired");
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
            $server,
            $botconfig,
            $botavatar
        );
        return true;
    }

    protected function processNoticeChange(
        Notice $notice,
        Rental &$rental,
        Package $package,
        Avatar $avatar,
        Stream $stream,
        Server $server,
        Botconfig $botconfig,
        Avatar $botavatar
    ): void {
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
            $botconfig,
            $botavatar,
            $avatar,
            $sendmessage,
            $notice->getUseBot()
        );
        if ($sendMessage_status["status"] == false) {
            $this->setSwapTag("message", "Unable to put mail into outbox");
            return;
        }
        $rental->setNoticeLink($notice->getId());
        $save_status = $rental->updateEntry();
        if ($save_status["status"] == false) {
            $this->setSwapTag("message", "Unable to update rental notice level");
            return;
        }

        if ($notice->getHoursRemaining() == 0) {
            $apilogic = new ApiLogicExpire();
            $reply = $apilogic->getApiServerLogicReply();
            if ($reply["status"] == false) {
                $this->setSwapTag("message", "API server logic has failed on ApiLogicExpire: " . $reply["message"]);
                return;
            }
        }

        if ($notice->getSendNotecard() == true) {
            if ($botconfig->getNotecards() == true) {
                $notecard = new Notecard();
                $notecard->setRentalLink($rental->getId());
                $notecard->setAsNotice(1);
                $notecard->setNoticeLink($notice->getId());
                $create_status = $notecard->createEntry();
                if ($create_status["status"] == false) {
                    $this->setSwapTag("message", "Unable to create new notecard");
                    return;
                }
            }
        }

        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        if ($notice->getNoticeNotecardLink() <= 1) {
            return;
        }

        $notice_notecard = new Noticenotecard();

        if ($notice_notecard->loadID($notice->getNoticeNotecardLink()) == false) {
            $this->setSwapTag("message", "Unable to find static notecard!");
            return;
        }
        if ($notice_notecard->getMissing() == false) {
            $this->setSwapTag("send_static_notecard", $notice_notecard->getName());
            $this->setSwapTag("send_static_notecard_to", $avatar->getAvatarUUID());
        }
        return;
    }
}
