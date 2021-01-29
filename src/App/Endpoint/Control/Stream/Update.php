<?php

namespace App\Endpoint\Control\Stream;

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
        $packageLink = $input->postFilter("packageLink", "integer");
        $serverLink = $input->postFilter("serverLink", "integer");
        $mountpoint = $input->postFilter("mountpoint");
        $adminUsername = $input->postFilter("adminUsername");
        $adminPassword = $input->postFilter("adminPassword");
        $djPassword = $input->postFilter("djPassword");
        $originalAdminUsername = $input->postFilter("originalAdminUsername");
        $apiConfigValue1 = $input->postFilter("apiConfigValue1");
        $apiConfigValue2 = $input->postFilter("apiConfigValue2");
        $apiConfigValue3 = $input->postFilter("apiConfigValue3");
        $api_update = $input->postFilter("api_update", "integer");

        if ($port < 1) {
            $this->setSwapTag("message", "Port must be 1 or more");
            return;
        }
        if ($port > 99999) {
            $this->setSwapTag("message", "Port must be 99999 or less");
            return;
        }
        if ($package->loadID($packageLink) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        if ($server->loadID($serverLink) == false) {
            $this->setSwapTag("message", "Unable to find server");
            return;
        }
        if (strlen($adminUsername) < 3) {
            $this->setSwapTag("message", "Admin username length must be 3 or more");
            return;
        }
        if (strlen($adminUsername) >= 50) {
            $this->setSwapTag("message", "Admin username length must be 50 or less");
            return;
        }
        if (strlen($adminPassword) < 4) {
            $this->setSwapTag("message", "Admin password length must be 4 or more");
            return;
        }
        if (strlen($adminPassword) > 20) {
            $this->setSwapTag("message", "Admin password length must be 20 or less");
            return;
        }
        if (strlen($djPassword) < 4) {
            $this->setSwapTag("message", "DJ password length must be 4 or more");
            return;
        }
        if (strlen($djPassword) > 20) {
            $this->setSwapTag("message", "DJ password length must be 20 or less");
            return;
        }
        if (strlen($originalAdminUsername) < 3) {
            $this->setSwapTag("message", "Original admin username length must be 3 or more");
            return;
        }
        if (strlen($originalAdminUsername) >= 50) {
            $this->setSwapTag("message", "Original admin username length must be 50 or less");
            return;
        }

        $stream = new Stream();
        if ($stream->loadByField("streamUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find stream with that uid");
            return;
        }
        $whereConfig = [
            "fields" => ["port","serverLink"],
            "values" => [$port,$serverLink],
            "types" => ["i","i"],
            "matches" => ["=","="],
        ];
        $count_check = $this->sql->basicCountV2($stream->getTable(), $whereConfig);
        $expected_count = 0;
        if ($stream->getPort() == $port) {
            if ($stream->getServerLink() == $serverLink) {
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
        $stream->setPackageLink($packageLink);
        $stream->setServerLink($serverLink);
        $stream->setPort($port);
        $stream->setNeedWork(false);
        $stream->setAdminUsername($adminUsername);
        $stream->setAdminPassword($adminPassword);
        $stream->setDjPassword($djPassword);
        $stream->setMountpoint($mountpoint);
        $stream->setOriginalAdminUsername($originalAdminUsername);
        $stream->setApiConfigValue1($apiConfigValue1);
        $stream->setApiConfigValue2($apiConfigValue2);
        $stream->setApiConfigValue3($apiConfigValue3);
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
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Stream updated");
        $this->setSwapTag("redirect", "stream");
    }
}
