<?php

$status = false;
$rental = new rental();
if ($rental->loadByField("rental_uid", $this->page) == true) {
    $stream = new stream();
    if ($stream->loadID($rental->getStreamlink()) == true) {
        $server_api_helper = new serverapi_helper($stream);
        $functionname = "api_" . $optional . "";
        if (method_exists($server_api_helper, $functionname) == true) {
            $status = $server_api_helper->$functionname();
            if (is_string($server_api_helper->getMessage()) == true) {
                if ($status == true) {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.api.passed"], $server_api_helper->getMessage()));
                } else {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.api.failed"], $server_api_helper->getMessage()));
                }
            } else {
                if ($status == true) {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.api.passed"], "No message from api helper"));
                } else {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.api.failed"], "No message from api helper"));
                }
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", "Unable to load api: " . $functionname);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["client.api.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["client.api.error.1"]);
}
