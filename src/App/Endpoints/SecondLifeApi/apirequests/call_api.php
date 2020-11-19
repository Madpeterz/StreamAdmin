<?php

$stream = new stream();
$soft_fail = false;
$status = false;
$message = "Started call_api";
$current_step = $functionname;
$retry = false;
if ($stream->loadID($api_request->get_streamlink()) == true) {
    $server_api_helper = new serverapi_helper($stream);
    if (method_exists($server_api_helper, $current_step) == true) {
        $status = $server_api_helper->$functionname();
        $message = $server_api_helper->getMessage();
        if ($status == true) {
            if ($retry == false) {
                $remove_status = $api_request->remove_me();
                if ($remove_status["status"] == true) {
                    $why_failed = "";
                    if ($logic_step != "opt") {
                        include "shared/media_server_apis/logic/" . $logic_step . ".php";
                        $status = $api_serverlogic_reply;
                        if ($status == true) {
                            $message = "ok reply from " . $logic_step . " - " . $functionname . "";
                        } else {
                            $message = $why_failed;
                        }
                    } else {
                        $message = "ok";
                    }
                } else {
                    $message = "Unable to remove old api request";
                }
            } else {
                $message = "retry";
                $status = true;
            }
        } else {
            $message = "API call " . $functionname . " failed with: " . $message . "";
        }
    } else {
        $message = "Unable to run api function: " . $functionname . " because: its missing";
    }
} else {
    $message = "Unable to load stream";
}
