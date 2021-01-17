<?php

namespace App\Endpoints\Control\Stream;

use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $package = new Package();
        $server = new Server();
        $input = new InputFilter();

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

        if ($port < 1) {
            $this->setSwapTag("message", "Port must be 1 or more");
            return;
        }
        if ($port > 99999) {
            $this->setSwapTag("message", "Port must be 99999 or less");
            return;
        }
        if ($package->loadID($packagelink) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        if ($server->loadID($serverlink) == false) {
            $this->setSwapTag("message", "Unable to find server");
            return;
        }
        if (strlen($adminusername) < 3) {
            $this->setSwapTag("message", "Admin username length must be 3 or more");
            return;
        }
        if (strlen($adminusername) >= 50) {
            $this->setSwapTag("message", "Admin username length must be 50 or less");
            return;
        }
        if (strlen($adminpassword) < 4) {
            $this->setSwapTag("message", "Admin password length must be 4 or more");
            return;
        }
        if (strlen($adminpassword) > 20) {
            $this->setSwapTag("message", "Admin password length must be 20 or less");
            return;
        }
        if (strlen($djpassword) < 4) {
            $this->setSwapTag("message", "DJ password length must be 4 or more");
            return;
        }
        if (strlen($djpassword) > 20) {
            $this->setSwapTag("message", "DJ password length must be 20 or less");
            return;
        }
        if (strlen($original_adminusername) < 3) {
            $this->setSwapTag("message", "Original admin username length must be 3 or more");
            return;
        }
        if (strlen($original_adminusername) >= 50) {
            $this->setSwapTag("message", "Original admin username length must be 50 or less");
            return;
        }

        $stream = new Stream();
        if ($stream->loadByField("stream_uid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find stream with that uid");
            return;
        }
        $whereConfig = [
            "fields" => ["port","serverlink"],
            "values" => [$port,$serverlink],
            "types" => ["i","i"],
            "matches" => ["=","="],
        ];
        $count_check = $this->sql->basicCountV2($stream->getTable(), $whereConfig);
        $expected_count = 0;
        if ($stream->getPort() == $port) {
            if ($stream->getServerlink() == $serverlink) {
                $expected_count = 1;
            }
        }
        if ($count_check["status"] == false) {
            $this->setSwapTag("message", "Unable to check if there is a stream on that port already!");
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->setSwapTag(
                "message",
                "There is already a stream on that port for the selected server!"
            );
            return;
        }
        $stream->setPackagelink($packagelink);
        $stream->setServerlink($serverlink);
        $stream->setPort($port);
        $stream->setNeedwork(false);
        $stream->setAdminusername($adminusername);
        $stream->setAdminpassword($adminpassword);
        $stream->setDjpassword($djpassword);
        $stream->setMountpoint($mountpoint);
        $stream->setOriginal_adminusername($original_adminusername);
        $stream->setApi_uid_1($api_uid_1);
        $stream->setApi_uid_2($api_uid_2);
        $stream->setApi_uid_3($api_uid_3);
        $update_status = $stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to update stream: %1\$s",
                    $update_status["message"]
                )
            );
            return;
        }
        $status = true;
        $why_failed = "";
        if ($api_update == 1) {
            include "shared/media_server_apis/logic/update.php";
            $status = $api_serverlogic_reply;
        }
        if ($status == false) {
            $this->setSwapTag("message", $why_failed);
            return;
        }
        $this->setSwapTag("message", "Stream updated");
        $this->setSwapTag("redirect", "stream");
    }
}
