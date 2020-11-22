<?php

$input = new inputFilter();
$accept = $input->postFilter("accept");
$status = false;
$redirect = "client/manage/" . $this->page . "";
$ajax_reply->set_swap_tag_string("redirect", null);
if ($accept == "Accept") {
    $rental = new rental();
    if ($rental->loadByField("rental_uid", $this->page) == true) {
        $api_requests = new api_requests_set();
        $all_ok = true;
        if ($api_requests->loadByField("rentallink", $rental->getId()) == true) {
            if ($api_requests->getCount() > 0) {
                $all_ok = false;
                echo  sprintf($lang["client.rm.error.13"], $api_requests->getCount());
            }
        } else {
            $all_ok = false;
            echo $lang["client.rm.error.12"];
        }
        if ($all_ok == true) {
            $stream = new stream();
            if ($stream->loadID($rental->getStreamlink()) == true) {
                $stream->set_rentallink(null);
                $stream->set_needwork(1);
                $update_status = $stream->save_changes();
                $server = new server();
                if ($server->loadID($stream->getServerlink()) == true) {
                    if ($update_status["status"] == true) {
                        $package = new package();
                        if ($package->loadID($rental->getPackagelink()) == true) {
                            $avatar = new avatar();
                            if ($avatar->loadID($rental->getAvatarlink()) == true) {
                                $all_ok = true;
                                $message = "";
                                // Event storage engine
                                if ($slconfig->get_eventstorage() == true) {
                                    $event = new event();
                                    $event->set_avatar_uuid($avatar->get_avataruuid());
                                    $event->set_avatar_name($avatar->getAvatarname());
                                    $event->set_rental_uid($rental->getRental_uid());
                                    $event->set_package_uid($package->getPackage_uid());
                                    $event->set_event_remove(true);
                                    $event->set_unixtime(time());
                                    $event->set_expire_unixtime($rental->getExpireunixtime());
                                    $event->set_port($stream->getPort());
                                    $create_status = $event->create_entry();
                                    if ($create_status["status"] == false) {
                                        $all_ok = false;
                                        $message = $lang["client.rm.error.8"];
                                    }
                                }
                                if ($all_ok == true) {
                                    $remove_status = $rental->remove_me();
                                    $all_ok = $remove_status["status"];
                                    if ($remove_status["status"] == true) {
                                        $status = true;
                                        $ajax_reply->set_swap_tag_string("redirect", "client");
                                        $ajax_reply->set_swap_tag_string("message", $lang["client.rm.info.1"]);
                                    } else {
                                        $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.rm.error.7"], $remove_status["message"]));
                                    }
                                }
                                if ($all_ok == true) {
                                    $rental = null;
                                    include "shared/media_server_apis/logic/revoke.php";
                                    $all_ok = $api_serverlogic_reply;
                                    if ($status != true) {
                                        $ajax_reply->set_swap_tag_string("message", $why_failed);
                                    }
                                }
                                if ($all_ok == true) {
                                    $status = true;
                                    $ajax_reply->set_swap_tag_string("redirect", "client");
                                    $ajax_reply->set_swap_tag_string("message", $lang["client.rm.info.1"]);
                                }
                            } else {
                                $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.6"]);
                            }
                        } else {
                            $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.5"]);
                        }
                    } else {
                        $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.4"]);
                    }
                } else {
                    $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.9"]);
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.3"]);
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.3"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.2"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.1"]);
    $ajax_reply->set_swap_tag_string("redirect", null);
}
