<?php

namespace App\Endpoint\Control\Stream;

use App\MediaServer\Logic\ApiLogicUpdate;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Framework\ViewAjax;
use App\Models\Sets\StreamSet;

class Update extends ViewAjax
{
    public function process(): void
    {
        $package = new Package();
        $server = new Server();


        $port = $this->post("port")->checkInRange(1, 99999)->asInt();
        $packageLink = $this->post("packageLink")->checkGrtThanEq(1)->asInt();
        $serverLink = $this->post("serverLink")->checkGrtThanEq(1)->asInt();
        $mountpoint = $this->post("mountpoint")->asString();
        $adminUsername = $this->post("adminUsername")->checkStringLength(3, 50)->asString();
        $adminPassword = $this->post("adminPassword")->checkStringLength(4, 20)->asString();
        $djPassword = $this->post("djPassword")->checkStringLength(4, 20)->asString();
        $bits = [$port,$packageLink,$serverLink,$mountpoint,$adminUsername,$adminPassword,$djPassword];
        if (in_array(null, $bits, true) == true) {
            $this->failed($this->input->getWhyFailed());
            return false;
        }

        $stream = new Stream();
        if ($stream->loadByStreamUid($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find stream with that uid");
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
        if ($this->transferRentalPackage($stream, $package) == false) {
            $this->failed("Unable to transfer rental to new package");
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

    protected function transferRentalPackage(Stream $stream, Package $package): bool
    {
        $rental = new Rental();
        if ($rental->loadByStreamLink($stream->getId()) == null) {
            return true;
        }
        if ($package->getId() == $rental->getPackageLink()) {
            return true;
        }
        $rental->setPackageLink($package->getId());
        $status = $rental->updateEntry();
        return $status["status"];
    }
}
