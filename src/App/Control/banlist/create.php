<?php

$status = false;
$input = new inputFilter();
$avataruid = $input->postFilter("uid");

$avatar_where_config = [
    "fields" => ["avatar_uid","avatarname","avataruuid"],
    "matches" => ["=","=","="],
    "values" => [$avataruid,$avataruid,$avataruid],
    "types" => ["s","s","s"],
    "join_with" => ["(OR)","(OR)"],
];
$avatar_set = new avatar_set();
$avatar_set->loadWithConfig($avatar_where_config);

if ($avatar_set->getCount() == 1) {
    $avatar = $avatar_set->get_first();
    $banlist = new banlist();
    if ($banlist->loadByField("avatar_link", $avatar->getId()) == false) {
        $banlist = new banlist();
        $banlist->set_avatar_link($avatar->getId());
        $create_status = $banlist->create_entry();
        if ($create_status["status"] == true) {
            $status = true;
            $ajax_reply->set_swap_tag_string("message", $lang["banlist.create.ok"]);
            $ajax_reply->set_swap_tag_string("redirect", "banlist");
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["banlist.create.failed.unabletocreate"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["banlist.create.failed.avataralreadybanned"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["banlist.create.failed.noavatar"]);
}
