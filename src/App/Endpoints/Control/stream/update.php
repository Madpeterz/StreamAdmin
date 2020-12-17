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
    $this->output->setSwapTagString("message", $lang["stream.up.error.1"];
} elseif ($port > 99999) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.2"];
} elseif ($package->loadID($packagelink) == false) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.3"];
} elseif ($server->loadID($serverlink) == false) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.4"];
} elseif (strlen($adminusername) < 3) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.5"];
} elseif (strlen($adminusername) >= 50) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.6"];
} elseif (strlen($adminpassword) < 4) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.7"];
} elseif (strlen($adminpassword) > 20) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.8"];
} elseif (strlen($djpassword) < 4) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.9"];
} elseif (strlen($djpassword) > 20) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.10"];
} elseif (strlen($original_adminusername) < 3) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.5"];
} elseif (strlen($original_adminusername) >= 50) {
    $this->output->setSwapTagString("message", $lang["stream.up.error.6"];
}

$status = false;
if ($failed_on == "") {
    $stream = new stream();
    if ($stream->loadByField("stream_uid", $this->page) == true) {
        $where_fields = [["port" => "="],["serverlink" => "="]];
        $where_values = [[$port => "i"],[$serverlink => "i"]];
        $count_check = $sql->basic_count($stream->getTable(), $where_fields, $where_values);
        $expected_count = 0;
        if ($stream->getPort() == $port) {
            if ($stream->getServerlink() == $serverlink) {
                $expected_count = 1;
            }
        }
        if ($count_check["status"] == true) {
            if ($count_check["count"] == $expected_count) {
                $stream->setPackagelink($packagelink);
                $stream->set_serverlink($serverlink);
                $stream->set_port($port);
                $stream->setNeedwork(false);
                $stream->set_adminusername($adminusername);
                $stream->setAdminpassword($adminpassword);
                $stream->setDjpassword($djpassword);
                $stream->set_mountpoint($mountpoint);
                $stream->set_original_adminusername($original_adminusername);
                $stream->set_api_uid_1($api_uid_1);
                $stream->set_api_uid_2($api_uid_2);
                $stream->set_api_uid_3($api_uid_3);
                $update_status = $stream->updateEntry();
                if ($update_status["status"] == true) {
                    $status = true;
                    if ($api_update == 1) {
                        include "shared/media_server_apis/logic/update.php";
                        $all_ok = $api_serverlogic_reply;
                    }
                    if ($status != true) {
                        $this->output->setSwapTagString("message", $why_failed);
                    } else {
                        $this->output->setSwapTagString("message", $lang["stream.up.info.1"]);
                        $this->output->setSwapTagString("redirect", "stream");
                    }
                } else {
                    $this->output->setSwapTagString("message", sprintf($lang["stream.up.error.14"], $update_status["message"]));
                }
            } else {
                $this->output->setSwapTagString("message", $lang["stream.up.error.13"]);
            }
        } else {
            $this->output->setSwapTagString("message", $lang["stream.up.error.12"]);
        }
    } else {
        $this->output->setSwapTagString("message", $lang["stream.up.error.11"]);
    }
} else {
    $this->output->setSwapTagString("message", $failed_on);
}
