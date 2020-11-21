<?php

$api_logiclang = [
    "failed.create" => "Unable to create event",
    "failed.noapi" => "Unable to find API config",
    "failed.noserver" => "Unable to find server",
];
$api_serverlogic_reply = true;
if (isset($site_lang) == false) {
    $site_lang = "en";
}
$lang_file = "shared/lang/api_serverlogic/" . $site_lang . ".php";
if (file_exists($lang_file) == true) {
    include $lang_file;
}
if (isset($server) == false) {
    $server = new server();
    $server->loadID($stream->getServerlink());
}
if (isset($no_api_action) == false) {
    $no_api_action = true;
}
if (isset($rental) == false) {
    $rental = null;
}
if (isset($why_failed) == false) {
    $why_failed = "";
}
if (isset($current_step) == false) {
    $current_step = "";
}
if ($server->is_loaded() == true) {
    $api = new apis();
    if ($api->loadID($server->get_apilink()) == true) {
        if ($api->getId() != 1) {
            $exit = false;
            while ($exit == false) {
                if (array_key_exists($current_step, $steps) == true) {
                    $current_step = $steps[$current_step];
                } else {
                    $current_step = "none";
                }
                if ($current_step != "none") {
                    $has_api_step = true;
                    if ($current_step != "core_send_details") {
                        $has_api_step = false;
                        $getName = "get_" . $current_step . "";
                        if (($api->$getName() == 1) && ($server->$getName() == 1)) {
                            $has_api_step = true;
                        }
                    }
                    if ($has_api_step == true) {
                        $all_ok = true;
                        $exit = true;
                        if ($current_step == "core_send_details") {
                            if ($rental == null) {
                                $rental = new rental();
                                $all_ok = $rental->loadByField("streamlink", $stream->getId());
                            }
                        }
                        if ($all_ok == true) {
                            $no_api_action = false;
                            $api_serverlogic_reply = create_pending_api_request($server, $stream, $rental, $current_step, $api_logiclang["failed.create"], true);
                        } else {
                            $api_serverlogic_reply = false;
                        }
                    } else {
                        if ($current_step == "event_recreate_revoke") {
                            $current_step = "recreate_not_enabled";
                        }
                    }
                } else {
                    $exit = true;
                }
            }
        }
    } else {
        $all_ok = false;
        echo $api_logiclang["failed.noapi"];
    }
} else {
    $all_ok = false;
    echo $lang["failed.noserver"];
}
