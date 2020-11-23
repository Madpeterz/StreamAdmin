<?php

$load_ok = false;
$notecard = new notecard();
$rental = new rental();
$package = new package();
$avatar = new avatar();
$template = new template();
$stream = new stream();
$server = new server();
$notice = new notice();
$load_by = [
    "rental" => ["notecard" => "rentallink"],
    "avatar" => ["rental" => "avatarlink"],
    "stream" => ["rental" => "streamlink"],
    "server" => ["stream" => "serverlink"],
    "package" => ["stream" => "packagelink"],
];
if ($notecard->get_as_notice() == false) {
    $load_by["template"] = ["package" => "templatelink"];
} else {
    $load_by["notice"] = ["rental" => "noticelink"];
}
$notecard_set = new notecard_set();
$notecard_set->load_newest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
if ($notecard_set->getCount() > 0) {
    $notecard = $notecard_set->get_first();
    $load_ok = true;
    foreach ($load_by as $objectname => $value) {
        foreach ($value as $source => $linkon) {
            $object = $$objectname;
            $loadfromobject = $$source;
            $loadfromfunction = "get_" . $linkon . "";
            if ($object->loadID($loadfromobject->$loadfromfunction()) == false) {
                $load_ok = false;
                break;
            }
        }
    }
}
if ($load_ok == true) {
    $notecard_title = "";
    $notecard_content = "";
    $swap_helper = new swapables_helper();
    if ($notecard->get_as_notice() == false) {
        $notecard_title = "Streamdetails for " . $avatar->getAvatarname() . " port: " . $stream->getPort() . "";
        $notecard_content = $swap_helper->get_swapped_text($template->getNotecarddetail(), $avatar, $rental, $package, $server, $stream);
    } else {
        $notecard_title = "Reminder for " . $avatar->getAvatarname() . " port: " . $stream->getPort() . "";
        $notecard_content = $swap_helper->get_swapped_text($notice->getNotecarddetail(), $avatar, $rental, $package, $server, $stream);
    }
    $remove_status = $notecard->remove_me();
    if ($remove_status["status"] == true) {
        $reply = [
            "status" => true,
            "message" => "ok",
            "AvatarUUID" => $avatar->get_avataruuid(),
            "NotecardTitle" => $notecard_title,
            "NotecardContent" => $notecard_content,
        ];
    } else {
        $reply = ["status" => false,"message" => "Unable to load notecard right now"];
    }
} else {
    $reply = ["status" => false,"message" => "Unable to load notecard right now"];
}
