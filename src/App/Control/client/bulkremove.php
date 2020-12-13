<?php

$whereconfig = [
    "fields" => ["expireunixtime"],
    "values" => [time()],
    "types" => ["i"],
    "matches" => ["<="],
];
$input = new inputFilter();
$template_parts["page_actions"] = "";
$rental_set = new rental_set();
$stream_set = new stream_set();
$package_set = new package_set();
$avatar_set = new avatar_set();
$server_set = new server_set();
$api_requests_set = new api_requests_set();
$apis_set = new apis_set();
$rental_set->loadWithConfig($whereconfig);
$avatar_set->loadIds($rental_set->getAllByField("avatarlink"));
$package_set->loadIds($rental_set->getAllByField("packagelink"));
$stream_set->loadIds($rental_set->getAllByField("streamlink"));
$api_requests_set->loadIds($rental_set->getAllByField("id"), "rentallink");
$apis_set->loadAll();
$server_set->loadAll();
$removed_counter = 0;
$skipped_counter = 0;
$status = true;
$ajax_reply->set_swap_tag_string("redirect", "client/bulkremove");
$rental_ids_removed = [];
$status = true;
foreach ($rental_set->getAllIds() as $rental_id) {
    $rental = $rental_set->getObjectByID($rental_id);
    if (strlen($rental->getMessage()) == 0) {
        if ($api_requests_set->get_object_by_field("rentallink", $rental_id) == null) {
            $accept = $input->postFilter("rental" . $rental->getRental_uid() . "");
            if ($accept == "purge") {
                $stream = $stream_set->getObjectByID($rental->getStreamlink());
                if ($stream != null) {
                    $server = new server();
                    if ($server->loadID($stream->getServerlink()) == true) {
                        $stream->set_rentallink(null);
                        $stream->set_needwork(1);
                        $update_status = $stream->updateEntry();
                        if ($update_status["status"] == true) {
                            $all_ok = true;
                            $message = "";
                            if ($slconfig->get_eventstorage() == true) {
                                $package = $package_set->getObjectByID($rental->getPackagelink());
                                $avatar = $avatar_set->getObjectByID($rental->getAvatarlink());
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
                                    $message = $lang["client.br.error.1"];
                                }
                            }
                            // Server API support
                            if ($all_ok == true) {
                                $remove_status = $rental->remove_me();
                                $all_ok = $remove_status["status"];
                                if ($remove_status["status"] == true) {
                                    $status = true;
                                    $ajax_reply->set_swap_tag_string("redirect", "client");
                                    $ajax_reply->set_swap_tag_string("message", $lang["client.rm.info.1"]);
                                } else {
                                    $message = sprintf($lang["client.rm.error.7"], $remove_status["message"]);
                                }
                            }
                            if ($all_ok == true) {
                                $rental = null;
                                include "shared/media_server_apis/logic/revoke.php";
                                $all_ok = $api_serverlogic_reply;
                                if ($status != true) {
                                    $message = $why_failed;
                                }
                            }
                            if ($all_ok == true) {
                                $removed_counter++;
                            } else {
                                $status = false;
                                $ajax_reply->set_swap_tag_string("message", $message);
                                break;
                            }
                        } else {
                            $status = false;
                            $ajax_reply->set_swap_tag_string("message", $lang["client.rm.error.4"]);
                            break;
                        }
                    } else {
                        $status = false;
                        $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.br.error.5"], $rental->getRental_uid()));
                        break;
                    }
                } else {
                    $status = false;
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.br.error.4"], $rental->getRental_uid()));
                    break;
                }
            }
        } else {
            $skipped_counter++;
        }
    }
}
if ($status == true) {
    $status = true;
    $ajax_reply->set_swap_tag_string("message", sprintf($lang["client.br.info.1"], $removed_counter, $skipped_counter));
    $ajax_reply->set_swap_tag_string("redirect", "client");
}
