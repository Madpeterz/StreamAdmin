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
        global $stream;
        $package = new Package();
        $server = new Server();
        $input = new InputFilter();
        $port = $input->postInteger("port");
        $packageLink = $input->postInteger("packageLink");
        $serverLink = $input->postInteger("serverLink");
        $mountpoint = $input->postString("mountpoint");
        $adminUsername = $input->postString("adminUsername", 50, 3);
        if ($adminUsername == null) {
            $this->failed("Admin username failed:" . $input->getWhyFailed());
            return;
        }
        $adminPassword = $input->postString("adminPassword", 20, 4);
        if ($adminPassword == null) {
            $this->failed("Admin password failed:" . $input->getWhyFailed());
            return;
        }
        $djPassword = $input->postString("djPassword", 20, 4);
        if ($djPassword == null) {
            $this->failed("DJ password failed:" . $input->getWhyFailed());
            return;
        }
        $needswork = $input->postBool("needswork");
        $apiConfigValue1 = $input->postString("apiConfigValue1");
        $apiConfigValue2 = $input->postString("apiConfigValue2");
        $apiConfigValue3 = $input->postString("apiConfigValue3");
        $api_create = $input->postBool("api_create");
        if ($port < 1) {
            $this->failed("Port must be 1 or more");
            return;
        }
        if ($port > 99999) {
            $this->failed("Port must be 99999 or less");
            return;
        }
        if ($package->loadID($packageLink) == false) {
            $this->failed("Unable to find package");
            return;
        }
        if ($server->loadID($serverLink) == false) {
            $this->failed("Unable to find server");
            return;
        }
        $stream = new Stream();
        $uid = $stream->createUID("streamUid", 8);
        if ($uid["status"] == false) {
            $this->failed("Unable to assign a new UID to the stream");
            return;
        }
        $whereConfig = [
            "fields" => ["port","serverLink"],
            "values" => [$port,$serverLink],
            "types" => ["i","i"],
            "matches" => ["=","="],
        ];
        $count_check = $this->sql->basicCountV2($stream->getTable(), $whereConfig);
        if ($count_check["status"] == false) {
            $this->failed("Unable to check if there is a stream on that port already!");
            return;
        }
        if ($count_check["count"] != 0) {
            $this->failed(
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
            $this->failed(
                sprintf(
                    "Unable to create stream: %1\$s",
                    $create_status["message"]
                )
            );
            return;
        }
        $this->ok("Stream created");
        if ($api_create == false) {
            $this->setSwapTag("redirect", "stream");
            return;
        }
        $apilogic = null;
        $apilogic = new ApiLogicCreate();
        $apilogic->setStream($stream);
        $apilogic->setServer($server);
        $reply = $apilogic->createNextApiRequest();
        if ($reply["status"] == false) {
            $this->failed("Bad reply: " . $reply["message"]);
            return;
        }
        $this->ok("Stream creation underway");
    }
}
