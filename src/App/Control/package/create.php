<?php

$template = new template();
$servertype = new servertypes();
$input = new inputFilter();
$name = $input->postFilter("name");
$templatelink = $input->postFilter("templatelink", "integer");
$cost = $input->postFilter("cost", "integer");
$days = $input->postFilter("days", "integer");
$bitrate = $input->postFilter("bitrate", "integer");
$listeners = $input->postFilter("listeners", "integer");
$texture_uuid_soldout = $input->postFilter("texture_uuid_soldout", "uuid");
$texture_uuid_instock_small = $input->postFilter("texture_uuid_instock_small", "uuid");
$texture_uuid_instock_selected = $input->postFilter("texture_uuid_instock_selected", "uuid");
$autodj = $input->postFilter("autodj", "bool");
$autodj_size = $input->postFilter("autodj_size", "integer");
$api_template = $input->postFilter("api_template");
$servertypelink = $input->postFilter("servertypelink", "integer");

$failed_on = "";
if (strlen($name) < 5) {
    $failed_on .= $lang["package.cr.error.1"];
} elseif (strlen($name) > 60) {
    $failed_on .= $lang["package.cr.error.2"];
} elseif ($cost < 1) {
    $failed_on .= $lang["package.cr.error.3"];
} elseif ($cost > 99999) {
    $failed_on .= $lang["package.cr.error.4"];
} elseif ($days < 1) {
    $failed_on .= $lang["package.cr.error.5"];
} elseif ($days > 999) {
    $failed_on .= $lang["package.cr.error.6"];
} elseif ($bitrate < 56) {
    $failed_on .= $lang["package.cr.error.7"];
} elseif ($bitrate > 999) {
    $failed_on .= $lang["package.cr.error.8"];
} elseif ($listeners < 1) {
    $failed_on .= $lang["package.cr.error.9"];
} elseif ($listeners > 999) {
    $failed_on .= $lang["package.cr.error.10"];
} elseif (strlen($texture_uuid_soldout) != 36) {
    $failed_on .= $lang["package.cr.error.11"];
} elseif (strlen($texture_uuid_instock_small) != 36) {
    $failed_on .= $lang["package.cr.error.12"];
} elseif (strlen($texture_uuid_instock_selected) != 36) {
    $failed_on .= $lang["package.cr.error.13"];
} elseif ($autodj_size > 9999) {
    $failed_on .= $lang["package.cr.error.14"];
} elseif ($template->loadID($templatelink) == false) {
    $failed_on .= $lang["package.cr.error.15"];
} elseif (strlen($api_template) > 50) {
    $failed_on .= $lang["package.cr.error.18"];
} elseif (strlen($api_template) < 3) {
    $failed_on .= $lang["package.cr.error.19"];
} elseif ($servertype->loadID($servertypelink) == false) {
    $failed_on .= $lang["package.cr.error.20"];
}

$this->output->setSwapTagString("redirect", "package");
$status = false;
if ($failed_on == "") {
    $package = new package();
    $uid = $package->createUID("package_uid", 8, 10);
    if ($uid["status"] == true) {
        $package->set_package_uid($uid["uid"]);
        $package->set_name($name);
        $package->set_autodj($autodj);
        $package->set_autodj_size($autodj_size);
        $package->set_listeners($listeners);
        $package->set_bitrate($bitrate);
        $package->set_templatelink($templatelink);
        $package->set_cost($cost);
        $package->set_days($days);
        $package->set_texture_uuid_soldout($texture_uuid_soldout);
        $package->set_texture_uuid_instock_small($texture_uuid_instock_small);
        $package->set_texture_uuid_instock_selected($texture_uuid_instock_selected);
        $package->set_api_template($api_template);
        $package->set_servertypelink($servertypelink);
        $create_status = $package->createEntry();
        if ($create_status["status"] == true) {
            $status = true;
            $this->output->setSwapTagString("message", $lang["package.cr.info.1"]);
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["package.cr.error.17"], $create_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $lang["package.cr.error.16"]);
    }
} else {
    $this->output->setSwapTagString("message", $failed_on);
    $this->output->setSwapTagString("redirect", "");
}
