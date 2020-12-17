<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "template");
$status = false;
if ($accept == "Accept") {
    $template = new template();
    if ($template->loadID($this->page) == true) {
        $remove_status = $templateremoveEntry();
        if ($remove_status["status"] == true) {
            $status = true;
            $this->output->setSwapTagString("message", $lang["template.rm.info.1"]);
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["template.cr.error.6"], $remove_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $lang["tempalte.rm.error.2"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["tempalte.rm.error.1"]);
    $this->output->setSwapTagString("redirect", "template/manage/" . $this->page . "");
}
