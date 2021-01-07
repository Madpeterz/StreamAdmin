<?php

namespace App\Endpoints\SecondLifeApi\Noticeserver;

use App\Models\Apis;
use App\Models\Avatar;
use App\Models\Botconfig;
use App\Models\Notecard;
use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Models\NoticeSet;
use App\Models\Package;
use App\Models\Rental;
use App\Models\RentalSet;
use App\Models\Server;
use App\Models\Stream;
use App\Template\SecondlifeAjax;
use bot_helper;
use swapables_helper;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        global $unixtime_hour;
        if ($this->owner_override == false) {
            $this->output->setSwapTagString("message", "SystemAPI access only - please contact support");
            return;
        }

        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) == false) {
            $this->output->setSwapTagString("message", "Unable to load bot config");
            return;
        }

        if ($botconfig->getAvatarlink() <= 0) {
            $this->output->setSwapTagString("message", "Assigned avatar to bot is not vaild");
            return;
        }

        $botavatar = new Avatar();
        if ($botavatar->loadID($botconfig->getAvatarlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load avatar attached to bot");
            return;
        }

        $server = new Server();
        $apis = new Apis();
        $notice_set = new NoticeSet();
        $package = new Package();
        $stream = new Stream();
        $avatar = new Avatar();
        $rental_set = new RentalSet();

        $notice_set->loadAll();

        $rental_ids_expired = [];
        $status = true;
        $why_failed = "";
        $all_ok = true;
        $changes = 0;

        $sorted_linked = $notice_set->getLinkedArray("hoursremaining", "id");
        ksort($sorted_linked, SORT_NUMERIC);
        $max_hours = array_keys($sorted_linked)[count($sorted_linked) - 2]; // ignore 999 hours at the end for active
        $unixtime = $max_hours * $unixtime_hour;
        $expired_notice = $notice_set->getObjectByField("hoursremaining", 0);

        $where_config = [
            "fields" => ["expireunixtime","noticelink"],
            "values" => [(time() + $unixtime),$expired_notice->getId()],
            "types" => ["i","i"],
            "matches" => ["<=","!="],
        ];

        $rental_set->loadWithConfig($where_config);
        if ($rental_set->getCount() == 0) {
            $this->output->setSwapTagString("status", "true");
            $this->output->setSwapTagString("message", "nowork");
            return;
        }

        $rental = $rental_set->getFirst();

        $avatar->loadID($rental->getAvatarlink());
        $stream->loadID($rental->getStreamlink());
        $server->loadID($stream->getServerlink());
        $package->loadID($stream->getPackagelink());
        $apis->loadID($server->getApilink());

        if ($rental->getExpireunixtime() < time()) {
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
            return;
        }

        $hours_remain = ceil(($rental->getExpireunixtime() - time()) / $unixtime_hour);
        if ($hours_remain < 0) {
            $this->output->setSwapTagString("message", "Math error - negitive hours remaining but not expired");
            return;
        }

        $current_notice_level = $notice_set->getObjectByID($rental->getNoticelink());
        $current_hold_hours = $current_notice_level->getHoursremaining();
        $use_notice_index = 0;
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

        if ($use_notice_index != 0) {
            if ($use_notice_index != $current_notice_level->getId()) {
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
                return;
            }
        }

        $this->output->setSwapTagString(
            "message",
            "Error processing notice change - End of process found! expected nowork call!"
        );
    }

    protected function processNoticeChange(
        Notice $notice,
        Rental $rental,
        Package $package,
        Avatar $avatar,
        Stream $stream,
        Server $server,
        Botconfig $botconfig,
        Avatar $botavatar
    ): void {
        $bot_helper = new bot_helper();
        $swapables_helper = new swapables_helper();
        $sendmessage = $swapables_helper->get_swapped_text(
            $notice->getImmessage(),
            $avatar,
            $rental,
            $package,
            $server,
            $stream
        );
        $send_message_status = $bot_helper->send_message(
            $botconfig,
            $botavatar,
            $avatar,
            $sendmessage,
            $notice->getUsebot()
        );
        if ($send_message_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to put mail into outbox");
            return;
        }
        $rental->setNoticelink($notice->getId());
        $save_status = $rental->updateEntry();
        if ($save_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to update rental notice level");
            return;
        }

        if ($notice->getHoursremaining() == 0) {
            include "shared/media_server_apis/logic/expire.php";
            $all_ok = $api_serverlogic_reply;
        }

        if ($all_ok == false) {
            return;
        }

        if ($notice->getSend_notecard() == true) {
            if ($botconfig->getNotecards() == true) {
                $notecard = new Notecard();
                $notecard->setRentallink($rental->getId());
                $notecard->setAs_notice(1);
                $notecard->setNoticelink($notice->getId());
                $create_status = $notecard->createEntry();
                if ($create_status["status"] == false) {
                    $this->output->setSwapTagString("message", "Unable to create new notecard");
                    return;
                }
            }
        }

        if ($notice->getNotice_notecardlink() <= 1) {
            return;
        }
        $notice_notecard = new Noticenotecard();

        if ($notice_notecard->loadID($notice->getNotice_notecardlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to find static notecard!");
            return;
        }
        if ($notice_notecardgetMissing() == false) {
            $this->output->setSwapTagString("send_static_notecard", $notice_notecard->getName());
            $this->output->setSwapTagString("send_static_notecard_to", $avatar->getAvataruuid());
        }

        $this->output->setSwapTagString("status", "true");
    }
}
