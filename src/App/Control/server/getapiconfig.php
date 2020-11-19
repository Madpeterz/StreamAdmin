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
        $ajax_reply->set_swap_tag_string("message", "API config loaded");
    } else {
        $ajax_reply->set_swap_tag_string("message", "Unknown API selected");
    }
} else {
    $ajax_reply->set_swap_tag_string("message", "Invaild API selected");
}
