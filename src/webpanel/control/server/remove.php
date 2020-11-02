<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "server");
$status = false;
if ($accept == "Accept") {
    $server = new server();
    if ($server->load($page) == true) {
        $stream_set = new stream_set();
        $load_status = $stream_set->load_on_field("serverlink", $server->get_id());
        if ($load_status["status"] == true) {
            if ($stream_set->get_count() == 0) {
                $api_requests_set = new api_requests_set();
                $load_status = $api_requests_set->load_on_field("serverlink", $server->get_id());
                if ($load_status["status"] == true) {
                    if ($api_requests_set->get_count() == 0) {
                        $remove_status = $server->remove_me();
                        if ($remove_status["status"] == true) {
                            $status = true;
                            $ajax_reply->set_swap_tag_string("message", $lang["server.rm.info.1"]);
                        } else {
                            $ajax_reply->set_swap_tag_string("message", sprintf($lang["server.rm.error.3"], $remove_status["message"]));
                        }
                    } else {
                        $ajax_reply->set_swap_tag_string("message", sprintf($lang["server.rm.error.6"], $api_requests_set->get_count()));
                    }
                } else {
                    $ajax_reply->set_swap_tag_string("message", $lang["server.rm.error.7"]);
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", sprintf($lang["server.rm.error.5"], $stream_set->get_count()));
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["server.rm.error.4"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["server.rm.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["server.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect", "server/manage/" . $page . "");
}
