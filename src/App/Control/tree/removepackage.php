<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "tree");
$status = false;
if ($accept == "Accept") {
    $treevender_packages = new treevender_packages();
    if ($treevender_packages->loadID($this->page) == true) {
        $redirect_to = $treevender_packages->get_treevenderlink();
        $remove_status = $treevender_packages->remove_me();
        if ($remove_status["status"] == true) {
            $status = true;
            $ajax_reply->set_swap_tag_string("redirect", "tree/manage/" . $redirect_to . "");
            $ajax_reply->set_swap_tag_string("message", $lang["tree.rp.info.1"]);
        } else {
            $ajax_reply->set_swap_tag_string("message", sprintf($lang["tree.rp.error.3"], $remove_status["message"]));
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["tree.rp.error.1"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["tree.rp.error.1"]);
}
