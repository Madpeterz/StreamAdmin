<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "textureconfig");
$status = false;
if ($accept == "Accept") {
    $textureconfig = new textureconfig();
    if ($textureconfig->load($this->page) == true) {
        $remove_status = $textureconfig->remove_me();
        if ($remove_status["status"] == true) {
            $status = true;
            $ajax_reply->set_swap_tag_string("message", $lang["textureconfig.rm.info.1"]);
        } else {
            $ajax_reply->set_swap_tag_string("message", sprintf($lang["textureconfig.cr.error.13"], $remove_status["message"]));
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["textureconfig.rm.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["textureconfig.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect", "textureconfig/manage/" . $this->page . "");
}
