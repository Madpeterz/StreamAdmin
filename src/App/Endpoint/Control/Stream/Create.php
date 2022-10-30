<?php

namespace App\Endpoint\Control\Stream;

use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Sets\StreamSet;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        $package = new Package();
        $server = new Server();

        $port = $this->input->post("port")->checkInRange(1, 99999)->asInt();
        $packageLink = $this->input->post("packageLink")->checkGrtThanEq(1)->asInt();
        $serverLink = $this->input->post("serverLink")->checkGrtThanEq(1)->asInt();
        $mountpoint = $this->input->post("mountpoint")->asString();
        $adminUsername = $this->input->post("adminUsername")->checkStringLength(3, 50)->asString();
        $adminPassword = $this->input->post("adminPassword")->checkStringLength(4, 20)->asString();
        $djPassword = $this->input->post("djPassword")->checkStringLength(4, 20)->asString();
        $bits = [$port,$packageLink,$serverLink,$mountpoint,$adminUsername,$adminPassword,$djPassword];
        if (in_array(null, $bits, true) == true) {
            $this->failed($this->input->getWhyFailed());
            return;
        }
        $needswork = $this->input->post("needswork")->asBool();
        if ($package->loadID($packageLink)->status == false) {
            $this->failed("Unable to find package");
            return;
        }
        if ($server->loadID($serverLink)->status == false) {
            $this->failed("Unable to find server");
            return;
        }
        $stream = new Stream();
        $uid = $stream->createUID("streamUid", 8);
        if ($uid->status == false) {
            $this->failed("Unable to assign a new UID to the stream");
            return;
        }
        $whereConfig = [
            "fields" => ["port","serverLink"],
            "values" => [$port,$serverLink],
            "types" => ["i","i"],
            "matches" => ["=","="],
        ];
        $streamSet = new StreamSet();
        $count_check = $streamSet->countInDB($whereConfig);
        if ($count_check === null) {
            $this->failed("Unable to check if there is a stream on that port already!");
            return;
        }
        if ($count_check != 0) {
            $this->failed(
                "There is already a stream on that port for the selected server!"
            );
            return;
        }
        $stream->setStreamUid($uid->uid);
        $stream->setPackageLink($packageLink);
        $stream->setServerLink($serverLink);
        $stream->setPort($port);
        $stream->setNeedWork($needswork);
        $stream->setAdminUsername($adminUsername);
        $stream->setAdminPassword($adminPassword);
        $stream->setDjPassword($djPassword);
        $stream->setMountpoint($mountpoint);
        $create_status = $stream->createEntry();
        if ($create_status->status == false) {
            $this->failed(
                sprintf(
                    "Unable to create stream: %1\$s",
                    $create_status->message
                )
            );
            return;
        }
        $this->redirectWithMessage("Stream created on port: " . $port);
    }
}
