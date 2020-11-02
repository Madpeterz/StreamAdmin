<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "reseller");
$status = false;
if ($accept == "Accept") {
    $reseller = new reseller();
    if ($reseller->load($page) == true) {
        $remove_status = $reseller->remove_me();
        if ($remove_status["status"] == true) {
            $status = true;
            $ajax_reply->set_swap_tag_string("message", $lang["reseller.rm.info.1"]);
        } else {
            $ajax_reply->set_swap_tag_string("message", sprintf($lang["reseller.rm.error.3"], $remove_status["message"]));
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["reseller.rm.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["reseller.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect", "reseller/manage/" . $page . "");
}
