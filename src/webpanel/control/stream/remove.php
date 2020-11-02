<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$ajax_reply->set_swap_tag_string("redirect", "stream");
$status = false;
if ($accept == "Accept") {
    $stream = new stream();
    if ($stream->load_by_field("stream_uid", $page) == true) {
        $transaction_set = new transactions_set();
        $load_status = $transaction_set->load_on_field("streamlink", $stream->get_id());
        if ($load_status["status"] == true) {
            $unlink_ok = true;
            $bulkupdate_status = array("status" => false,"message" => "not run");
            if ($transaction_set->get_count() > 0) {
                $unlink_ok = false;
                $bulkupdate_status = $transaction_set->update_single_field_for_collection("streamlink", null);
                if ($bulkupdate_status["status"] == true) {
                    $unlink_ok = true;
                }
            }
            if ($unlink_ok == true) {
                $remove_status = $stream->remove_me();
                if ($remove_status["status"] == true) {
                    $status = true;
                    $ajax_reply->set_swap_tag_string("message", $lang["stream.rm.info.1"]);
                } else {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["stream.rm.error.3"], $remove_status["message"]));
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", sprintf($lang["stream.rm.error.5"], $bulkupdate_status["message"]));
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", sprintf($lang["stream.rm.error.4"], $load_status["message"]));
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["stream.rm.error.2"]);
    }
} else {
    $status = false;
    $ajax_reply->set_swap_tag_string("message", $lang["stream.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect", "stream/manage/" . $page . "");
}
