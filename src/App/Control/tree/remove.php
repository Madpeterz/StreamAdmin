<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "tree");
$status = false;
if ($accept == "Accept") {
    $treevender = new treevender();
    if ($treevender->loadID($this->page) == true) {
        $treevender_package_set = new treevender_packages_set();
        $treevender_package_set->load_on_field("treevenderlink", $treevender->getId());
        $purge_status = $treevender_package_set->purge_collection_set();
        if ($purge_status["status"] == true) {
            $remove_status = $treevender->remove_me();
            if ($remove_status["status"] == true) {
                $status = true;
                $ajax_reply->set_swap_tag_string("message", $lang["tree.rm.info.1"]);
            } else {
                $ajax_reply->set_swap_tag_string("message", sprintf($lang["tree.rm.error.4"], $remove_status["message"]));
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["tree.rm.error.3"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["tree.rm.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("redirect", "tree/manage/" . $this->page . "");
    $ajax_reply->set_swap_tag_string("message", $lang["tree.rm.error.1"]);
}
