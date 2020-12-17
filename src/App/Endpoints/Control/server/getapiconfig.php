<?php

$input = new inputFilter();
$apilink = $input->postFilter("apilink", "integer");
$api = new apis();
$status = false;
if ($apilink > 0) {
    if ($api->loadID($apilink) == true) {
        foreach ($api->get_fields() as $apifield) {
            $getter = "get_" . $apifield;
            $reply[$apifield] = $api->$getter();
        }
        $status = true;
        $reply["update_api_flags"] = true;
        $this->output->setSwapTagString("message", "API config loaded");
    } else {
        $this->output->setSwapTagString("message", "Unknown API selected");
    }
} else {
    $this->output->setSwapTagString("message", "Invaild API selected");
}
