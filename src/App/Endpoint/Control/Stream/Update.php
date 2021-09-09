<?php

namespace App\Endpoint\Control\Stream;

use App\MediaServer\Logic\ApiLogicUpdate;
use App\R7\Model\Package;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
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
        }
        $adminPassword = $input->postString("adminPassword", 20, 4);
        if ($adminUsername == null) {
            $this->failed("Admin password failed:" . $input->getWhyFailed());
        }
        $djPassword = $input->postString("djPassword", 20, 4);
        if ($adminUsername == null) {
            $this->failed("DJ password failed:" . $input->getWhyFailed());
        }
        $originalAdminUsername = $input->postString("originalAdminUsername", 50, 3);
        if ($originalAdminUsername == null) {
            $this->failed("Original admin username failed:" . $input->getWhyFailed());
        }
        $apiConfigValue1 = $input->postString("apiConfigValue1");
        $apiConfigValue2 = $input->postString("apiConfigValue2");
        $apiConfigValue3 = $input->postString("apiConfigValue3");
        $api_update = $input->postBool("api_update");

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
        if ($stream->loadByField("streamUid", $this->page) == false) {
            $this->failed("Unable to find stream with that uid");
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
            $this->failed("Unable to check if there is a stream on that port already!");
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
            $this->failed(
                sprintf(
                    "Unable to update stream: %1\$s",
                    $update_status["message"]
                )
            );
            return;
        }

        if ($api_update == true) {
            $apilogic = new ApiLogicUpdate();
            $apilogic->setStream($stream);
            $apilogic->setServer($server);
            $reply = $apilogic->createNextApiRequest();
            if ($reply["status"] == false) {
                $this->failed("Bad reply: " . $reply["message"]);
                return;
            }
        }
        $this->ok("Stream updated");
        $this->setSwapTag("redirect", "stream");
    }
}
