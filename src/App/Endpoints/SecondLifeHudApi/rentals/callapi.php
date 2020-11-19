<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$request_code = $input->postFilter("apiid");
$rental = new rental();
$status = false;
$why_failed = "not processed";
$accepted_api_calls = ["opt_toggle_autodj","opt_password_reset","opt_autodj_next"];
if (in_array($request_code, $accepted_api_calls) == true) {
    if ($rental->loadByField("rental_uid", $rental_uid) == true) {
        if ($rental->getAvatarlink() == $object_owner_avatar->getId()) {
            if ($rental->get_expireunixtime() > time()) {
                $stream = new stream();
                if ($stream->load($rental->get_streamlink()) == true) {
                    $server = new server();
                    if ($server->load($stream->get_serverlink()) == true) {
                        $pendingapi = new api_requests_set();
                        $pendingapi->loadByField("streamlink", $rental->get_streamlink());
                        if ($pendingapi->getCount() == 0) {
                            $status = create_pending_api_request($server, $stream, $rental, $request_code, "Unable to create event %1\$s because: %2\$s", true);
                            if ($status == false) {
                                $why_failed = "Unable to save pending api request";
                            } else {
                                echo "accepted request is now in the Q";
                            }
                        } else {
                            $why_failed = "pending api calls - please wait and try again";
                        }
                    } else {
                        $why_failed = "Unable to load server attached to stream";
                    }
                } else {
                    $why_failed = "Unable to load stream attached to rental";
                }
            } else {
                $why_failed = "rental is expired - api disabled";
            }
        } else {
            $why_failed = "rental ownership issue";
        }
    } else {
        $why_failed = "Unable to load rental";
    }
} else {
    $why_failed = "unknown public api call made";
}
if ($status == false) {
    echo $why_failed;
}
