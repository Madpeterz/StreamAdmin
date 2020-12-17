<?php

$package = new package();
$server = new server();
$input = new inputFilter();

$name = $input->postFilter("name");
$getting_details = $input->postFilter("getting_details");
$request_details = $input->postFilter("request_details");
$offline = $input->postFilter("offline");
$wait_owner = $input->postFilter("wait_owner");
$inuse = $input->postFilter("inuse");
$make_payment = $input->postFilter("make_payment");
$stock_levels = $input->postFilter("stock_levels");
$renew_here = $input->postFilter("renew_here");
$proxyrenew = $input->postFilter("proxyrenew");
$treevend_waiting = $input->postFilter("treevend_waiting");
$failed_on = "";
if (strlen($name) < 4) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.1"];
} elseif (strlen($name) > 30) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.2"];
} elseif (strlen($getting_details) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.3"];
} elseif (strlen($request_details) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.4"];
} elseif (strlen($offline) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.5"];
} elseif (strlen($wait_owner) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.6"];
} elseif (strlen($inuse) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.7"];
} elseif (strlen($make_payment) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.8"];
} elseif (strlen($stock_levels) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.9"];
} elseif (strlen($renew_here) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.10"];
} elseif (strlen($proxyrenew) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.11"];
} elseif (strlen($treevend_waiting) != 36) {
    $this->output->setSwapTagString("message", $lang["textureconfig.up.error.12"];
}
$status = false;
if ($failed_on == "") {
    $textureconfig = new textureconfig();
    if ($textureconfig->loadID($this->page) == true) {
        $textureconfig->setName($name);
        $textureconfig->set_offline($offline);
        $textureconfig->set_wait_owner($wait_owner);
        $textureconfig->set_stock_levels($stock_levels);
        $textureconfig->set_make_payment($make_payment);
        $textureconfig->set_inuse($inuse);
        $textureconfig->set_renew_here($renew_here);
        $textureconfig->set_getting_details($getting_details);
        $textureconfig->set_request_details($request_details);
        $textureconfig->set_proxyrenew($proxyrenew);
        $textureconfig->set_treevend_waiting($treevend_waiting);
        $update_status = $textureconfig->updateEntry();
        if ($update_status["status"] == true) {
            $status = true;
            $this->output->setSwapTagString("message", $lang["textureconfig.up.info.1"]);
            $this->output->setSwapTagString("redirect", "textureconfig");
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["textureconfig.up.error.14"], $update_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $lang["textureconfig.up.error.13"]);
        $this->output->setSwapTagString("redirect", "textureconfig");
    }
} else {
    $this->output->setSwapTagString("message", $failed_on);
}
