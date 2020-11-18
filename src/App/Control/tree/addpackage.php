<?php

$input = new inputFilter();
$package_id = $input->postFilter("package", "integer");

$status = false;
$treevender = new treevender();
$ajax_reply->set_swap_tag_string("redirect", "tree");
if ($treevender->load($page) == true) {
    if ($package_id > 0) {
        $package = new package();
        if ($package->load($package_id) == true) {
            $treevender_package = new treevender_packages();
            $where_fields = [
                "fields" => ["packagelink","treevenderlink"],
                "values" => [$package->getId(),$treevender->getId()],
                "types" => ["i","i"],
                "matches" => ["=","="],
            ];
            if ($treevender_package->load_with_config($where_fields) == false) {
                $treevender_package = new treevender_packages();
                $treevender_package->set_packagelink($package->getId());
                $treevender_package->set_treevenderlink($treevender->getId());
                $create_status = $treevender_package->create_entry();
                if ($create_status["status"] == true) {
                    $ajax_reply->set_swap_tag_string("redirect", "tree/manage/" . $page . "");
                    $ajax_reply->set_swap_tag_string("message", $lang["tree.ap.info.1"]);
                    $status = true;
                } else {
                    $ajax_reply->set_swap_tag_string("message", $lang["tree.ap.error.5"]);
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", $lang["tree.ap.error.4"]);
                $ajax_reply->set_swap_tag_string("redirect", "");
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["tree.ap.error.3"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["tree.ap.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["tree.ap.error.1"]);
}
