<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "staff");
$status = false;
if ($accept == "Accept") {
    $staff = new staff();
    if ($staff->loadID($this->page) == true) {
        if ($staff->getOwnerLevel() == false) {
            $remove_status = $staffremoveEntry();
            if ($remove_status["status"] == true) {
                $status = true;
                $this->output->setSwapTagString("message", $lang["staff.rm.info.1"]);
            } else {
                $this->output->setSwapTagString("message", sprintf($lang["staff.cr.error.10"], $remove_status["message"]));
            }
        } else {
            $this->output->setSwapTagString("message", $lang["staff.rm.error.1"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["staff.rm.error.1"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["staff.rm.error.1"]);
    $this->output->setSwapTagString("redirect", "staff/manage/" . $this->page . "");
}
