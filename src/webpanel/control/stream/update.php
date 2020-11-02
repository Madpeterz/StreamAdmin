<?php

$package = new package();
$server = new server();
$input = new inputFilter();
$port = $input->postFilter("port", "integer");
$packagelink = $input->postFilter("packagelink", "integer");
$serverlink = $input->postFilter("serverlink", "integer");
$mountpoint = $input->postFilter("mountpoint");
$adminusername = $input->postFilter("adminusername");
$adminpassword = $input->postFilter("adminpassword");
$djpassword = $input->postFilter("djpassword");
$original_adminusername = $input->postFilter("original_adminusername");
$api_uid_1 = $input->postFilter("api_uid_1");
$api_uid_2 = $input->postFilter("api_uid_2");
$api_uid_3 = $input->postFilter("api_uid_3");
$api_update = $input->postFilter("api_update", "integer");

$failed_on = "";
if ($port < 1) {
    $failed_on .= $lang["stream.up.error.1"];
} elseif ($port > 99999) {
    $failed_on .= $lang["stream.up.error.2"];
} elseif ($package->load($packagelink) == false) {
    $failed_on .= $lang["stream.up.error.3"];
} elseif ($server->load($serverlink) == false) {
    $failed_on .= $lang["stream.up.error.4"];
} elseif (strlen($adminusername) < 3) {
    $failed_on .= $lang["stream.up.error.5"];
} elseif (strlen($adminusername) >= 50) {
    $failed_on .= $lang["stream.up.error.6"];
} elseif (strlen($adminpassword) < 4) {
    $failed_on .= $lang["stream.up.error.7"];
} elseif (strlen($adminpassword) > 20) {
    $failed_on .= $lang["stream.up.error.8"];
} elseif (strlen($djpassword) < 4) {
    $failed_on .= $lang["stream.up.error.9"];
} elseif (strlen($djpassword) > 20) {
    $failed_on .= $lang["stream.up.error.10"];
} elseif (strlen($original_adminusername) < 3) {
    $failed_on .= $lang["stream.up.error.5"];
} elseif (strlen($original_adminusername) >= 50) {
    $failed_on .= $lang["stream.up.error.6"];
}

$status = false;
if ($failed_on == "") {
    $stream = new stream();
    if ($stream->load_by_field("stream_uid", $page) == true) {
        $where_fields = array(array("port" => "="),array("serverlink" => "="));
        $where_values = array(array($port => "i"),array($serverlink => "i"));
        $count_check = $sql->basic_count($stream->get_table(), $where_fields, $where_values);
        $expected_count = 0;
        if ($stream->get_port() == $port) {
            if ($stream->get_serverlink() == $serverlink) {
                $expected_count = 1;
            }
        }
        if ($count_check["status"] == true) {
            if ($count_check["count"] == $expected_count) {
                $stream->set_packagelink($packagelink);
                $stream->set_serverlink($serverlink);
                $stream->set_port($port);
                $stream->set_needwork(false);
                $stream->set_adminusername($adminusername);
                $stream->set_adminpassword($adminpassword);
                $stream->set_djpassword($djpassword);
                $stream->set_mountpoint($mountpoint);
                $stream->set_original_adminusername($original_adminusername);
                $stream->set_api_uid_1($api_uid_1);
                $stream->set_api_uid_2($api_uid_2);
                $stream->set_api_uid_3($api_uid_3);
                $update_status = $stream->save_changes();
                if ($update_status["status"] == true) {
                    $status = true;
                    if ($api_update == 1) {
                        include "shared/media_server_apis/logic/update.php";
                        $all_ok = $api_serverlogic_reply;
                    }
                    if ($status != true) {
                        $ajax_reply->set_swap_tag_string("message", $why_failed);
                    } else {
                        $ajax_reply->set_swap_tag_string("message", $lang["stream.up.info.1"]);
                        $ajax_reply->set_swap_tag_string("redirect", "stream");
                    }
                } else {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["stream.up.error.14"], $update_status["message"]));
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", $lang["stream.up.error.13"]);
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["stream.up.error.12"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["stream.up.error.11"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $failed_on);
}
