<?php

namespace App\Endpoint\Control\Stream;

use App\MediaServer\Logic\ApiLogicCreate;
use App\R7\Model\Package;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
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
        $needswork = $input->postFilter("needswork", "bool");
        $apiConfigValue1 = $input->postFilter("apiConfigValue1");
        $apiConfigValue2 = $input->postFilter("apiConfigValue2");
        $apiConfigValue3 = $input->postFilter("apiConfigValue3");
        $api_create = $input->postFilter("api_create", "integer");

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
        $stream = new Stream();
        $uid = $stream->createUID("streamUid", 8, 10);
        if ($uid["status"] == false) {
            $this->setSwapTag("message", "Unable to assign a new UID to the stream");
            return;
        }

        $whereConfig = [
            "fields" => ["port","serverLink"],
            "values" => [$port,$serverLink],
            "types" => ["i","i"],
            "matches" => [">=","="],
        ];
        $count_check = $this->sql->basicCountV2($stream->getTable(), $whereConfig);
        if ($count_check["status"] == false) {
            $this->setSwapTag("message", "Unable to check if there is a stream on that port already!");
            return;
        }
        if ($count_check["count"] != 0) {
            $this->setSwapTag(
                "message",
                "There is already a stream on that port for the selected server!"
            );
            return;
        }

        $stream->setStreamUid($uid["uid"]);
        $stream->setPackageLink($packageLink);
        $stream->setServerLink($serverLink);
        $stream->setPort($port);
        $stream->setNeedWork($needswork);
        $stream->setAdminUsername($adminUsername);
        $stream->setAdminPassword($adminPassword);
        $stream->setOriginalAdminUsername($adminUsername);
        $stream->setDjPassword($djPassword);
        $stream->setMountpoint($mountpoint);
        $stream->setApiConfigValue1($apiConfigValue1);
        $stream->setApiConfigValue2($apiConfigValue2);
        $stream->setApiConfigValue3($apiConfigValue3);
        $create_status = $stream->createEntry();

        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to create stream: %1\$s",
                    $create_status["message"]
                )
            );
            return;
        }

        $api_serverlogic_reply = true;
        $apilogic = null;
        if ($api_create == 1) {
            $apilogic = new ApiLogicCreate();
        }
        if ($api_serverlogic_reply == false) {
            $this->setSwapTag("message", $apilogic->getApiServerLogicReply()["message"]);
            return;
        }
        $this->setSwapTag("status", $api_serverlogic_reply);
        $this->setSwapTag("message", "Stream created");
        if ($api_serverlogic_reply == true) {
            $this->setSwapTag("redirect", "stream");
        }
    }
}
