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
$needswork = $input->postFilter("needswork", "bool");
$api_uid_1 = $input->postFilter("api_uid_1");
$api_uid_2 = $input->postFilter("api_uid_2");
$api_uid_3 = $input->postFilter("api_uid_3");
$api_create = $input->postFilter("api_create", "integer");

$failed_on = "";
if ($port < 1) {
    $failed_on .= $lang["stream.cr.error.1"];
} elseif ($port > 99999) {
    $failed_on .= $lang["stream.cr.error.2"];
} elseif ($package->loadID($packagelink) == false) {
    $failed_on .= $lang["stream.cr.error.3"];
} elseif ($server->loadID($serverlink) == false) {
    $failed_on .= $lang["stream.cr.error.4"];
} elseif (strlen($adminusername) < 3) {
    $failed_on .= $lang["stream.cr.error.5"];
} elseif (strlen($adminusername) >= 50) {
    $failed_on .= $lang["stream.cr.error.6"];
} elseif (strlen($adminpassword) < 4) {
    $failed_on .= $lang["stream.cr.error.7"];
} elseif (strlen($adminpassword) > 20) {
    $failed_on .= $lang["stream.cr.error.8"];
} elseif (strlen($djpassword) < 4) {
    $failed_on .= $lang["stream.cr.error.9"];
} elseif (strlen($djpassword) > 20) {
    $failed_on .= $lang["stream.cr.error.10"];
}
$status = false;
if ($failed_on == "") {
    $stream = new stream();
    $uid = $stream->createUID("stream_uid", 8, 10);
    if ($uid["status"] == true) {
        $where_fields = [["port" => ">="],["serverlink" => "="]];
        $where_values = [[$port => "i"],[$serverlink => "i"]];
        $count_check = $sql->basic_count($stream->get_table(), $where_fields, $where_values);
        if ($count_check["status"] == true) {
            if ($count_check["count"] == 0) {
                $stream->set_stream_uid($uid["uid"]);
                $stream->setPackagelink($packagelink);
                $stream->set_serverlink($serverlink);
                $stream->set_port($port);
                $stream->setNeedwork($needswork);
                $stream->set_adminusername($adminusername);
                $stream->set_adminpassword($adminpassword);
                $stream->set_original_adminusername($adminusername);
                $stream->set_djpassword($djpassword);
                $stream->set_mountpoint($mountpoint);
                $stream->set_api_uid_1($api_uid_1);
                $stream->set_api_uid_2($api_uid_2);
                $stream->set_api_uid_3($api_uid_3);
                $create_status = $stream->createEntry();
                if ($create_status["status"] == true) {
                    $status = true;
                    if ($api_create == 1) {
                        include "shared/media_server_apis/logic/create.php";
                        $all_ok = $api_serverlogic_reply;
                    }
                    if ($status != true) {
                        $this->output->setSwapTagString("message", $why_failed);
                    } else {
                        $this->output->setSwapTagString("message", $lang["stream.cr.info.1"]);
                        $this->output->setSwapTagString("redirect", "stream");
                    }
                } else {
                    $this->output->setSwapTagString("message", sprintf($lang["stream.cr.error.14"], $create_status["message"]));
                }
            } else {
                $this->output->setSwapTagString("message", $lang["stream.cr.error.13"]);
            }
        } else {
            $this->output->setSwapTagString("message", $lang["stream.cr.error.12"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["stream.cr.error.11"]);
    }
} else {
    $this->output->setSwapTagString("message", $failed_on);
}
