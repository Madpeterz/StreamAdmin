<?php

$status = false;
$server = new server();
if ($server->loadID($this->page) == true) {
    $api = new apis();
    if ($api->loadID($server->getApilink()) == true) {
        if (($server->get_api_sync_accounts() == true) && ($api->get_api_sync_accounts() == true)) {
            $serverapi_helper = new serverapi_helper();
            if ($serverapi_helper->force_set_server($server) == true) {
                $oneday_ago = time() - ((60 * 60) * 24);
                $where_config = [
                    "fields" => ["serverlink","last_api_sync"],
                    "matches" => ["=","<="],
                    "values" => [$server->getId(),$oneday_ago],
                    "types" => ["i","i"],
                ];
                $limits = [
                             "page_number" => 0,
                             "max_entrys" => 10,
                ];
                $stream_set = new stream_set();
                $stream_set->loadWithConfig($where_config, null, $limits);
                if ($stream_set->getCount() > 0) {
                    $accounts_found = $serverapi_helper->get_all_accounts(true, $stream_set);
                    if ($accounts_found["status"] == true) {
                        $accounts_updated = 0;
                        $accounts_insync = 0;
                        $accounts_missing_global = 0;
                        $accounts_missing_passwords = 0;
                        $all_ok = true;
                        foreach ($stream_set->getAllIds() as $streamid) {
                            $stream = $stream_set->getObjectByID($streamid);
                            if (in_array($stream->getAdminusername(), $accounts_found["usernames"]) == true) {
                                if (array_key_exists($stream->getAdminusername(), $accounts_found["passwords"]) == true) {
                                    $has_update = false;
                                    if ($stream->getAdminpassword() != $accounts_found["passwords"][$stream->getAdminusername()]["admin"]) {
                                        $has_update = true;
                                        $stream->set_adminpassword($accounts_found["passwords"][$stream->getAdminusername()]["admin"]);
                                    }
                                    if ($stream->getDjpassword() != $accounts_found["passwords"][$stream->getAdminusername()]["dj"]) {
                                        $has_update = true;
                                        $stream->set_djpassword($accounts_found["passwords"][$stream->getAdminusername()]["dj"]);
                                    }
                                    $stream->set_last_api_sync(time());
                                    if ($has_update == true) {
                                        $update_status = $stream->save_changes();
                                        if ($update_status["status"] == true) {
                                            $accounts_updated++;
                                        } else {
                                            $all_ok = false;
                                            $ajax_reply->set_swap_tag_string("message", "failed to sync password to db");
                                            break;
                                        }
                                    } else {
                                        $update_status = $stream->save_changes();
                                        if ($update_status["status"] == true) {
                                            $accounts_insync++;
                                        } else {
                                            $all_ok = false;
                                            $ajax_reply->set_swap_tag_string("message", "Failed to mark stream as in sync");
                                            break;
                                        }
                                    }
                                } else {
                                    $accounts_missing_passwords++;
                                }
                            } else {
                                $accounts_missing_global++;
                            }
                        }
                        if ($all_ok == true) {
                            $server->set_last_api_sync(time());
                            $update_status = $server->save_changes();
                            if ($update_status["status"] == true) {
                                $status = true;
                                $ajax_reply->set_swap_tag_string("message", "Updated: " . $accounts_updated . " / Ok: " . $accounts_insync . "");
                                if ($accounts_missing_passwords > 0) {
                                    $ajax_reply->addSwapTagString("message", " / Missing PW dataset: " . $accounts_missing_passwords);
                                }
                                if ($accounts_missing_global > 0) {
                                    $ajax_reply->addSwapTagString("message", " / Account missing: " . $accounts_missing_global);
                                }
                            } else {
                                $ajax_reply->set_swap_tag_string("message", "Unable to update server last sync time");
                            }
                        }
                    } else {
                        $ajax_reply->set_swap_tag_string("message", $server_api_helper->getMessage());
                    }
                } else {
                    $ajax_reply->set_swap_tag_string("message", "Unable to find any streams attached to server or all streamed sync'd in the last 24 hours");
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", "Unable to attach server to api helper");
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", "Server or API have sync accounts disabled");
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", "Unable to find api used by server");
    }
} else {
    $ajax_reply->set_swap_tag_string("message", "Unable to find server");
}
