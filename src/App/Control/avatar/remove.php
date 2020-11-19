<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "avatar");
$status = false;
$ajax_reply->set_swap_tag_string("message", $lang["av.cr.info.1"]);
if ($accept == "Accept") {
    $avatar = new avatar();
    if ($avatar->loadByField("avatar_uid", $this->page) == true) {
        $remove_status = $avatar->remove_me();
        if ($remove_status["status"] == true) {
            $status = true;
            $ajax_reply->set_swap_tag_string("message", $lang["av.rm.info.1"]);
        } else {
            $ajax_reply->set_swap_tag_string("message", sprintf($lang["av.rm.error.3"], $remove_status["message"]));
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["av.rm.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["av.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect", "avatar/manage/" . $this->page . "");
}
