<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "notice");
$status = false;
if ($accept == "Accept") {
    if (in_array($page, array(6,10)) == false) {
        $notice = new notice();
        if ($notice->load($page) == true) {
            $notecard_set = new notecard_set();
            $load_status = $notecard_set->load_on_field("noticelink", $notice->get_id());
            if ($load_status["status"] == true) {
                if ($notecard_set->get_count() == 0) {
                    $remove_status = $notice->remove_me();
                    if ($remove_status["status"] == true) {
                        $status = true;
                        $ajax_reply->set_swap_tag_string("message", $lang["notice.rm.info.1"]);
                    } else {
                        $ajax_reply->set_swap_tag_string("message", sprintf($lang["notice.rm.error.4"], $remove_status["message"]));
                    }
                } else {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["notice.rm.error.6"], $notecard_set->get_count()));
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", $lang["notice.rm.error.5"]);
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["notice.rm.error.3"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["notice.rm.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["notice.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect", "notice/manage/" . $page . "");
}
