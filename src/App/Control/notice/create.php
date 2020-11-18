<?php

$notice = new notice();
$static_notecard = new notice_notecard();
$input = new inputFilter();
$name = $input->postFilter("name");
$hoursremaining = $input->postFilter("hoursremaining", "integer");
$immessage = $input->postFilter("immessage");
$usebot = $input->postFilter("usebot", "bool");
$send_notecard = $input->postFilter("send_notecard", "bool");
$notecarddetail = $input->postFilter("notecarddetail");
$notice_notecardlink = $input->postFilter("notice_notecardlink", "integer");
if ($send_notecard == false) {
    if (strlen($notecarddetail) < 1) {
        $notecarddetail = " ";
    }
}



$failed_on = "";
$ajax_reply->set_swap_tag_string("redirect", null);
if (strlen($name) < 5) {
    $failed_on .= $lang["notice.cr.error.1"];
} elseif (strlen($name) > 100) {
    $failed_on .= $lang["notice.cr.error.2"];
} elseif (strlen($immessage) < 5) {
    $failed_on .= $lang["notice.cr.error.3"];
} elseif (strlen($immessage) > 800) {
    $failed_on .= $lang["notice.cr.error.4"];
} elseif (strlen($hoursremaining) < 0) {
    $failed_on .= $lang["notice.cr.error.5"];
} elseif (strlen($hoursremaining) > 999) {
    $failed_on .= $lang["notice.cr.error.6"];
} elseif ($notice->load_by_field("hoursremaining", $hoursremaining) == true) {
    $failed_on .= $lang["notice.cr.error.7"];
} elseif ($static_notecard->load($notice_notecardlink) == false) {
    $failed_on .= $lang["notice.cr.error.9"];
} elseif ($static_notecard->get_missing() == true) {
    $failed_on .= $lang["notice.cr.error.9"];
}

if ($failed_on == "") {
    $notice = new notice();
    $notice->set_name($name);
    $notice->set_immessage($immessage);
    $notice->set_usebot($usebot);
    $notice->set_hoursremaining($hoursremaining);
    $notice->set_send_notecard($send_notecard);
    $notice->set_notecarddetail($notecarddetail);
    $notice->set_notice_notecardlink($static_notecard->getId());
    $create_status = $notice->create_entry();
    if ($create_status["status"] == true) {
        $status = true;
        $ajax_reply->set_swap_tag_string("message", $lang["notice.cr.info.1"]);
        $ajax_reply->set_swap_tag_string("redirect", "notice");
    } else {
        $status = false;
        $ajax_reply->set_swap_tag_string("message", sprintf($lang["notice.cr.error.8"], $create_status["message"]));
    }
} else {
    $status = false;
    $ajax_reply->set_swap_tag_string("message", $failed_on);
}
