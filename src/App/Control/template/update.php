<?php

$input = new inputFilter();
$name = $input->postFilter("name");
$detail = $input->postFilter("detail");
$notecarddetail = $input->postFilter("notecarddetail");
$failed_on = "";
if (strlen($name) < 5) {
    $failed_on .= $lang["template.up.error.1"];
} elseif (strlen($name) > 30) {
    $failed_on .= $lang["template.up.error.2"];
} elseif (strlen($detail) < 5) {
    $failed_on .= $lang["template.up.error.3"];
} elseif (strlen($detail) > 800) {
    $failed_on .= $lang["template.up.error.4"];
} elseif (strlen($notecarddetail) < 5) {
    $failed_on = $lang["template.up.error.5"];
}
$status = false;
if ($failed_on == "") {
    $template = new template();
    if ($template->load($this->page) == true) {
        $template->set_name($name);
        $template->set_detail($detail);
        $template->set_notecarddetail($notecarddetail);
        $update_status = $template->save_changes();
        if ($update_status["status"] == true) {
            $status = true;
            $ajax_reply->set_swap_tag_string("message", $lang["template.up.info.1"]);
            $ajax_reply->set_swap_tag_string("redirect", "template");
        } else {
            $ajax_reply->set_swap_tag_string("message", sprintf($lang["template.up.error.7"], $update_status["message"]));
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["template.up.error.6"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $failed_on);
}
