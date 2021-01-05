<?php

namespace App\Endpoints\Control\Stream;

use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;
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

        if ($port < 1) {
            $this->output->setSwapTagString("message", "Port must be 1 or more");
            return;
        }
        if ($port > 99999) {
            $this->output->setSwapTagString("message", "Port must be 99999 or less");
            return;
        }
        if ($package->loadID($packagelink) == false) {
            $this->output->setSwapTagString("message", "Unable to find package");
            return;
        }
        if ($server->loadID($serverlink) == false) {
            $this->output->setSwapTagString("message", "Unable to find server");
            return;
        }
        if (strlen($adminusername) < 3) {
            $this->output->setSwapTagString("message", "Admin username length must be 3 or more");
            return;
        }
        if (strlen($adminusername) >= 50) {
            $this->output->setSwapTagString("message", "Admin username length must be 50 or less");
            return;
        }
        if (strlen($adminpassword) < 4) {
            $this->output->setSwapTagString("message", "Admin password length must be 4 or more");
            return;
        }
        if (strlen($adminpassword) > 20) {
            $this->output->setSwapTagString("message", "Admin password length must be 20 or less");
            return;
        }
        if (strlen($djpassword) < 4) {
            $this->output->setSwapTagString("message", "DJ password length must be 4 or more");
            return;
        }
        if (strlen($djpassword) > 20) {
            $this->output->setSwapTagString("message", "DJ password length must be 20 or less");
            return;
        }
        $stream = new Stream();
        $uid = $stream->createUID("stream_uid", 8, 10);
        if ($uid["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to assign a new UID to the stream");
            return;
        }

        $whereConfig = [
            "fields" => ["port","serverlink"],
            "values" => [$port,$serverlink],
            "types" => ["i","i"],
            "matches" => [">=","="],
        ];
        $count_check = $this->sql->basicCountV2($stream->getTable(), $whereConfig);
        if ($count_check["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to check if there is a stream on that port already!");
            return;
        }
        if ($count_check["count"] != 0) {
            $this->output->setSwapTagString(
                "message",
                "There is already a stream on that port for the selected server!"
            );
            return;
        }

        $stream->setStream_uid($uid["uid"]);
        $stream->setPackagelink($packagelink);
        $stream->setServerlink($serverlink);
        $stream->setPort($port);
        $stream->setNeedwork($needswork);
        $stream->setAdminusername($adminusername);
        $stream->setAdminpassword($adminpassword);
        $stream->setOriginal_adminusername($adminusername);
        $stream->setDjpassword($djpassword);
        $stream->setMountpoint($mountpoint);
        $stream->setApi_uid_1($api_uid_1);
        $stream->setApi_uid_2($api_uid_2);
        $stream->setApi_uid_3($api_uid_3);
        $create_status = $stream->createEntry();

        if ($create_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf(
                    "Unable to create stream: %1\$s",
                    $create_status["message"]
                )
            );
            return;
        }
        $api_serverlogic_reply = true;
        if ($api_create == 1) {
            include "shared/media_server_apis/logic/create.php";
        }
        if ($api_serverlogic_reply == false) {
            $this->output->setSwapTagString("message", $why_failed);
            return;
        }
        $this->output->setSwapTagString("message", "Stream created");
        $this->output->setSwapTagString("redirect", "stream");
    }
}
