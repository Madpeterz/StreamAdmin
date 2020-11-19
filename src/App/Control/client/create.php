<?php

$avatar = new avatar();
$stream = new stream();
$input = new inputFilter();
$avataruid = $input->postFilter("avataruid");
$streamuid = $input->postFilter("streamuid");
$daysremaining = $input->postFilter("daysremaining", "integer");
$status = false;
$ajax_reply->set_swap_tag_string("redirect", "client");
$failed_on = "";

$avatar_where_config = [
    "fields" => ["avatar_uid","avatarname","avataruuid"],
    "matches" => ["=","=","="],
    "values" => [$avataruid,$avataruid,$avataruid],
    "types" => ["s","s","s"],
    "join_with" => ["(OR)","(OR)"],
];
$avatar_set = new avatar_set();
$avatar_set->loadWithConfig($avatar_where_config);

$stream_where_config = [
    "fields" => ["port","stream_uid"],
    "matches" => ["=","="],
    "values" => [$streamuid,$streamuid],
    "types" => ["i","s"],
    "join_with" => ["(OR)"],
];
$stream_set = new stream_set();
$stream_set->loadWithConfig($stream_where_config);
if ($stream_set->getCount() == 1) {
    $stream = $stream_set->get_first();
}
if ($avatar_set->getCount() != 1) {
    $failed_on .= $lang["client.cr.error.5"];
} elseif ($stream_set->getCount() != 1) {
    $failed_on .= $lang["client.cr.error.6"];
} elseif ($daysremaining > 999) {
    $failed_on .= $lang["client.cr.error.3"];
} elseif ($daysremaining < 1) {
    $failed_on .= $lang["client.cr.error.4"];
} elseif ($stream->getRentallink() > 0) {
    $failed_on .= $lang["client.cr.error.7"];
}
$notice_set = new notice_set();
$notice_set->loadAll();
$sorted_linked = $notice_set->getLinkedArray("hoursremaining", "id");
ksort($sorted_linked, SORT_NUMERIC);
$hours_remain = $daysremaining * 24;
$use_notice_index = 0;
foreach ($sorted_linked as $hours => $index) {
    if ($hours > $hours_remain) {
        break;
    } else {
        $use_notice_index = $index;
    }
}
if ($failed_on == "") {
    $avatar = $avatar_set->get_first();
    $unixtime = time() + ($daysremaining * $unixtime_day);
    $rental = new rental();
    $uid = $rental->create_uid("rental_uid", 8, 10);
    if ($uid["status"] == true) {
        $rental->set_rental_uid($uid["uid"]);
        $rental->set_avatarlink($avatar->getId());
        $rental->set_packagelink($stream->get_packagelink());
        $rental->set_streamlink($stream->getId());
        $rental->set_startunixtime(time());
        $rental->set_expireunixtime($unixtime);
        $rental->set_avatarlink($avatar->getId());
        $rental->set_noticelink($use_notice_index);
        $create_status = $rental->create_entry();
        if ($create_status["status"] == true) {
            $stream->set_rentallink($rental->getId());
            $stream->set_needwork(0);
            $update_status = $stream->save_changes();
            if ($update_status["status"] == true) {
                $status = true;
                $ajax_reply->set_swap_tag_string("message", $lang["client.cr.info.1"]);
            } else {
                $ajax_reply->set_swap_tag_string("message", $lang["client.cr.error.10"]);
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.cr.error.9"], $create_status["message"]));
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["client.cr.error.8"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $failed_on);
    $ajax_reply->set_swap_tag_string("redirect", null);
}
