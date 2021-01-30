<?php

namespace App\Endpoint\View\Import;

use App\R7\Set\AvatarSet;
use App\R7\Set\NoticeSet;
use App\R7\Model\Rental;
use App\R7\Set\StreamSet;
use App\R4\UsersSet;

class Clients extends View
{
    public function process(): void
    {
        $r4_users_set = new UsersSet();
        $r4_users_set->reconnectSql($this->oldSqlDB);
        $r4_users_set->loadAll();

        $avatar_set = new AvatarSet();
        $avatar_set->loadAll();

        $stream_set = new StreamSet();
        $stream_set->loadAll();

        $stream_set_oldid_to_id = $stream_set->getLinkedArray("mountpoint", "id");
        $avatar_set_uuid_to_id = $avatar_set->getLinkedArray("avatarUUID", "id");

        $clients_created = 0;
        $clients_skipped_no_stream = 0;
        $clients_skipped_no_avatar = 0;
        $clients_skipped_bad_notice_level = 0;
        $all_ok = true;

        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $sorted_linked = $notice_set->getLinkedArray("hoursRemaining", "id");
        ksort($sorted_linked, SORT_NUMERIC);

        foreach ($r4_users_set->getAllIds() as $r4_user_id) {
            $r4_user = $r4_users_set->getObjectByID($r4_user_id);
            $find_stream = "r4|" . $r4_user->getItemid() . "";
            if (array_key_exists($find_stream, $stream_set_oldid_to_id) == false) {
                $clients_skipped_no_stream++;
                continue;
            }
            if (array_key_exists($r4_user->getSlkey(), $avatar_set_uuid_to_id) == false) {
                $clients_skipped_no_avatar++;
                continue;
            }
            $unix_time_remaining = $r4_user->getExpireunix() - time();
            $use_notice_index = 6;
            if ($unix_time_remaining > 0) {
                $use_notice_index = 10;
                $hours_remain = (($unix_time_remaining / 60) / 60);
                foreach ($sorted_linked as $hours => $index) {
                    if ($hours > $hours_remain) {
                        break;
                    } else {
                        $use_notice_index = $index;
                    }
                }
            }
            $stream = $stream_set->getObjectByID($stream_set_oldid_to_id[$find_stream]);
            $avatar = $avatar_set->getObjectByID($avatar_set_uuid_to_id[$r4_user->getSlkey()]);
            $rental = new Rental();
            $uid = $rental->createUID("rentalUid", 8, 10);
            if ($uid["status"] == false) {
                $this->output->addSwapTagString("page_content", "Unable to create rental Uid");
                $all_ok = false;
                break;
            }
            $rental->setRentalUid($uid["uid"]);
            $rental->setAvatarLink($avatar->getId());
            $rental->setPackageLink($stream->getPackageLink());
            $rental->setStreamLink($stream->getId());
            $rental->setStartUnixtime(time());
            $rental->setExpireUnixtime($r4_user->getExpireunix());
            $rental->setAvatarLink($avatar->getId());
            $rental->setNoticeLink($use_notice_index);
            $rental->setMessage($r4_user->getNotes());
            $create_status = $rental->createEntry();
            if ($create_status["status"] == false) {
                $this->output->addSwapTagString(
                    "page_content",
                    "Unable to create rental because: " . $create_status["message"]
                );
                $all_ok = false;
                break;
            }
            $stream->setRentalLink($rental->getId());
            $stream->setNeedWork(0);
            $update_status = $stream->updateEntry();
            if ($update_status["status"] == false) {
                $this->output->addSwapTagString("page_content", "Unable to update stream to link to rental");
                $all_ok = false;
                break;
            }
            $clients_created++;
        }
        if ($all_ok == false) {
            $this->sql->flagError();
            return;
        }
        $this->output->addSwapTagString(
            "page_content",
            "Created: " . $clients_created . " clients, "
            . $clients_skipped_no_stream . " skipped (No stream), "
            . $clients_skipped_no_avatar . " skipped (No avatar), <br/> <a href=\"[[url_base]]import\">Back to menu</a>"
        );
    }
}
