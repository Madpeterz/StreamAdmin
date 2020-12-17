<?php

$input = new inputFilter();
$rate = $input->postFilter("rate", "integer");
$allowed = $input->postFilter("allowed", "bool");
$failed_on = "";
if ($rate < 1) {
    $failed_on .= $lang["reseller.up.error.1"];
} elseif ($rate > 100) {
    $failed_on .= $lang["reseller.up.error.2"];
}
$status = false;
$this->output->setSwapTagString("redirect", "reseller");
if ($failed_on == "") {
    $reseller = new reseller();
    if ($reseller->loadID($this->page) == true) {
        $reseller->set_rate($rate);
        $reseller->set_allowed($allowed);
        $update_status = $reseller->updateEntry();
        if ($update_status["status"] == true) {
            $status = true;
            $this->output->setSwapTagString("message", $lang["reseller.up.info.1"]);
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["reseller.up.error.4"], $update_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $lang["reseller.up.error.3"]);
    }
} else {
    $status = false;
    $this->output->setSwapTagString("message", $failed_on);
    $this->output->setSwapTagString("redirect", "");
}
