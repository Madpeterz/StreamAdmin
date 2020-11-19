<?php

$status = false;
$ajax_reply->set_swap_tag_string("redirect", "config");
if ($session->get_ownerlevel() == true) {
    $input = new inputFilter();
    $avataruid = $input->postFilter("avataruid");
    $secret = $input->postFilter("secret");
    $notecards = $input->postFilter("notecards", "bool");
    $ims = $input->postFilter("ims", "bool");

    $failed_on = "";
    if (strlen($avataruid) != 8) {
        $failed_on .= $lang["bot.up.error.1"];
    } elseif (strlen($secret) < 8) {
        $failed_on .= $lang["bot.up.error.2"];
    }

    $status = false;
    if ($failed_on == "") {
        $botconfig = new botconfig();
        if ($botconfig->load(1) == true) {
            $avatar = new avatar();
            if ($avatar->loadByField("avatar_uid", $avataruid) == true) {
                $botconfig->set_avatarlink($avatar->getId());
                $botconfig->set_secret($secret);
                $botconfig->set_notecards($notecards);
                $botconfig->set_ims($ims);
                $save_changes = $botconfig->save_changes();
                if ($save_changes["status"] == true) {
                    $status = true;
                    $ajax_reply->set_swap_tag_string("redirect", null);
                    $ajax_reply->set_swap_tag_string("message", $lang["bot.up.info.1"]);
                } else {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["bot.up.error.6"], $save_changes["message"]));
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", $lang["bot.up.error.5"]);
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["bot.up.error.4"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $failed_on);
        $ajax_reply->set_swap_tag_string("redirect", null);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["bot.up.error.3"]);
}
