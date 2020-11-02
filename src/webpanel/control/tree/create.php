<?php

$treevender = new treevender();
$input = new inputFilter();
$name = $input->postFilter("name");
$failed_on = "";
if (strlen($name) < 5) {
    $failed_on .= $lang["tree.cr.error.1"];
} elseif (strlen($name) > 100) {
    $failed_on .= $lang["tree.cr.error.2"];
} elseif ($treevender->load_by_field("name", $name) == true) {
    $failed_on .= $lang["tree.cr.error.3"];
}
$status = false;
if ($failed_on == "") {
    $treevender = new treevender();
    $treevender->set_name($name);
    $create_status = $treevender->create_entry();
    if ($create_status["status"] == true) {
        $status = true;
        $ajax_reply->set_swap_tag_string("redirect", "tree");
        $ajax_reply->set_swap_tag_string("message", $lang["tree.cr.info.1"]);
    } else {
        $ajax_reply->set_swap_tag_string("message", sprintf($lang["tree.cr.error.4"], $create_status["message"]));
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $failed_on);
}
