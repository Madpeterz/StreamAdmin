<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "staff");
$status = false;
if ($accept == "Accept") {
    $staff = new staff();
    if ($staff->loadID($this->page) == true) {
        if ($staff->get_ownerlevel() == false) {
            $remove_status = $staff->remove_me();
            if ($remove_status["status"] == true) {
                $status = true;
                $ajax_reply->set_swap_tag_string("message", $lang["staff.rm.info.1"]);
            } else {
                $ajax_reply->set_swap_tag_string("message", sprintf($lang["staff.cr.error.10"], $remove_status["message"]));
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["staff.rm.error.1"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["staff.rm.error.1"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["staff.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect", "staff/manage/" . $this->page . "");
}
