<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "objects");
$status = false;
if ($accept == "Accept") {
    $objects_set = new objects_set();
    $objects_set->loadAll();
    $purge_status = $objects_set->purge_collection_set();
    if ($purge_status["status"] == true) {
        $status = true;
        $ajax_reply->set_swap_tag_string("message", $lang["objects.cl.info.1"]);
    } else {
        $ajax_reply->set_swap_tag_string("message", sprintf($lang["objects.cl.error.2"], $purge_status["message"]));
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["objects.cl.error.1"]);
}
