<?php

$input = new inputFilter();
$name = $input->postFilter("name");
$detail = $input->postFilter("detail");
$notecarddetail = $input->postFilter("notecarddetail");
$failed_on = "";
if (strlen($name) < 5) {
    $failed_on .= $lang["template.cr.error.1"];
} elseif (strlen($name) > 30) {
    $failed_on .= $lang["template.cr.error.2"];
} elseif (strlen($detail) < 5) {
    $failed_on .= $lang["template.cr.error.3"];
} elseif (strlen($detail) > 800) {
    $failed_on .= $lang["template.cr.error.4"];
} elseif (strlen($notecarddetail) < 5) {
    $failed_on = $lang["template.cr.error.5"];
}
$status = false;
if ($failed_on == "") {
    $template = new template();
    $template->set_name($name);
    $template->set_detail($detail);
    $template->set_notecarddetail($notecarddetail);
    $create_status = $template->createEntry();
    if ($create_status["status"] == true) {
        $status = true;
        $this->output->setSwapTagString("message", $lang["template.cr.info.1"]);
        $this->output->setSwapTagString("redirect", "template");
    } else {
        $this->output->setSwapTagString("message", sprintf($lang["template.cr.error.6"], $create_status["message"]));
    }
} else {
    $this->output->setSwapTagString("message", $failed_on);
}
