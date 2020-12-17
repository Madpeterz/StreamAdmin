<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$this->output->setSwapTagString("redirect", "reseller");
$status = false;
if ($accept == "Accept") {
    $reseller = new reseller();
    if ($reseller->loadID($this->page) == true) {
        $remove_status = $resellerremoveEntry();
        if ($remove_status["status"] == true) {
            $status = true;
            $this->output->setSwapTagString("message", $lang["reseller.rm.info.1"]);
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["reseller.rm.error.3"], $remove_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $lang["reseller.rm.error.2"]);
    }
} else {
    $this->output->setSwapTagString("message", $lang["reseller.rm.error.1"]);
    $this->output->setSwapTagString("redirect", "reseller/manage/" . $this->page . "");
}
