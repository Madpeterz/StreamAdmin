<?php

$input = new inputFilter();
$name = $input->postFilter("name");
$failed_on = "";
$ajax_reply->set_swap_tag_string("redirect", "");
if (strlen($name) < 5) {
    $failed_on .= $lang["tree.up.error.1"];
} elseif (strlen($name) > 100) {
    $failed_on .= $lang["tree.up.error.2"];
}
$status = false;
if ($failed_on == "") {
    $treevender = new treevender();
    if ($treevender->load($page) == true) {
        $where_fields = array(array("name" => "="));
        $where_values = array(array($name => "s"));
        $count_check = $sql->basic_count($treevender->get_table(), $where_fields, $where_values);
        $expected_count = 0;
        if ($treevender->get_name() == $name) {
            $expected_count = 1;
        }
        if ($count_check["status"] == true) {
            if ($count_check["count"] == $expected_count) {
                $treevender->set_name($name);
                $update_status = $treevender->save_changes();
                if ($update_status["status"] == true) {
                    $status = true;
                    $ajax_reply->set_swap_tag_string("redirect", "tree");
                    $ajax_reply->set_swap_tag_string("message", $lang["tree.up.info.1"]);
                } else {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["tree.up.error.6"], $update_status["message"]));
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", $lang["tree.up.error.5"]);
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["tree.up.error.4"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("redirect", "tree");
        $ajax_reply->set_swap_tag_string("message", $lang["tree.up.error.3"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $failed_on);
}
