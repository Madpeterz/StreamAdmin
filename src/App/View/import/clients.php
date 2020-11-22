<?php

$current_sql = $sql;
$old_sql = new mysqli_controler();
$old_sql->sqlStart_test($r4_db_username, $r4_db_pass, $r4_db_name, false, $r4_db_host);

$sql = $old_sql; // switch to r4

$r4_users_set = new r4_users_set();
$r4_users_set->loadAll();

$sql = $current_sql; // swtich back to r7

$avatar_set = new avatar_set();
$avatar_set->loadAll();

$stream_set = new stream_set();
$stream_set->loadAll();

$stream_set_oldid_to_id = $stream_set->getLinkedArray("mountpoint", "id");
$avatar_set_uuid_to_id = $avatar_set->getLinkedArray("avataruuid", "id");

$clients_created = 0;
$clients_skipped_no_stream = 0;
$clients_skipped_no_avatar = 0;
$clients_skipped_bad_notice_level = 0;
$all_ok = true;

include "shared/lang/control/client/" . $site_lang . ".php";

$notice_set = new notice_set();
$notice_set->loadAll();
$sorted_linked = $notice_set->getLinkedArray("hoursremaining", "id");
ksort($sorted_linked, SORT_NUMERIC);

foreach ($r4_users_set->getAllIds() as $r4_user_id) {
    $r4_user = $r4_users_set->getObjectByID($r4_user_id);
    $find_stream = "r4|" . $r4_user->get_itemid() . "";
    if (array_key_exists($find_stream, $stream_set_oldid_to_id) == true) {
        if (array_key_exists($r4_user->get_slkey(), $avatar_set_uuid_to_id) == true) {
            $unix_time_remaining = $r4_user->get_expireunix() - time();
            $use_notice_index = 0;
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
            } else {
                $use_notice_index = 6;
            }
            if ($use_notice_index > 0) {
                $stream = $stream_set->getObjectByID($stream_set_oldid_to_id[$find_stream]);
                $avatar = $avatar_set->getObjectByID($avatar_set_uuid_to_id[$r4_user->get_slkey()]);
                $rental = new rental();
                $uid = $rental->create_uid("rental_uid", 8, 10);
                if ($uid["status"] == true) {
                    $rental->set_rental_uid($uid["uid"]);
                    $rental->set_avatarlink($avatar->getId());
                    $rental->set_packagelink($stream->getPackagelink());
                    $rental->set_streamlink($stream->getId());
                    $rental->set_startunixtime(time());
                    $rental->set_expireunixtime($r4_user->get_expireunix());
                    $rental->set_avatarlink($avatar->getId());
                    $rental->set_noticelink($use_notice_index);
                    $rental->set_message($r4_user->get_notes());
                    $create_status = $rental->create_entry();
                    if ($create_status["status"] == true) {
                        $stream->set_rentallink($rental->getId());
                        $stream->set_needwork(0);
                        $update_status = $stream->save_changes();
                        if ($update_status["status"] == true) {
                            $clients_created++;
                        } else {
                            $this->output->addSwapTagString("page_content", $lang["client.cr.error.10"]);
                            $all_ok = false;
                            break;
                        }
                    } else {
                        $this->output->addSwapTagString("page_content", sprintf($lang["client.cr.error.9"], $create_status["message"]));
                        $all_ok = false;
                        break;
                    }
                } else {
                    $this->output->addSwapTagString("page_content", $lang["client.cr.error.8"]);
                    $all_ok = false;
                    break;
                }
            } else {
                $clients_skipped_bad_notice_level++;
            }
        } else {
            $clients_skipped_no_avatar++;
        }
    } else {
        $clients_skipped_no_stream++;
    }
}
if ($all_ok == true) {
    $this->output->addSwapTagString("page_content", "Created: " . $clients_created . " clients, " . $clients_skipped_no_stream . " skipped (No stream), " . $clients_skipped_no_avatar . " skipped (No avatar), " . $clients_skipped_bad_notice_level . " skipped (Bad notice level) <br/> <a href=\"[[url_base]]import\">Back to menu</a>");
} else {
    $sql->flagError();
}
