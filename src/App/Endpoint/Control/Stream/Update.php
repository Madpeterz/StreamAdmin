<?php

namespace App\Endpoint\Control\Stream;

use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Sets\StreamSet;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    public function process(): void
    {

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

        $package = new Package();
        $package->loadId($packageLink);
        if ($package->isLoaded() == false) {
            $this->failed("Unable to find package");
            return;
        }

        $stream = new Stream();
        if ($stream->loadByStreamUid($this->siteConfig->getPage())->status == false) {
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
        if ($count_check->status == false) {
            $this->failed("Unable to check if there is a stream on that port already!");
            return;
        }
        if ($count_check->items != $expected_count) {
            $this->setSwapTag(
                "message",
                "There is already a stream on that port for the selected server!"
            );
            return;
        }
        $oldvalues = $stream->objectToValueArray();
        $stream->setPackageLink($packageLink);
        $stream->setServerLink($serverLink);
        $stream->setPort($port);
        $stream->setNeedWork(false);
        $stream->setAdminUsername($adminUsername);
        $stream->setAdminPassword($adminPassword);
        $stream->setDjPassword($djPassword);
        $stream->setMountpoint($mountpoint);
        $update_status = $stream->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf(
                    "Unable to update stream: %1\$s",
                    $update_status->message
                )
            );
            return;
        }
        if ($this->transferRentalPackage($stream, $package) == false) {
            return;
        }
        $this->redirectWithMessage("Stream updated");
        $this->createMultiAudit(
            $stream->getStreamUid(),
            $stream->getFields(),
            $oldvalues,
            $stream->objectToValueArray()
        );
    }

    protected function transferRentalPackage(Stream $stream, Package $package): bool
    {
        $rental = new Rental();
        if ($stream->getRentalLink() == null) {
            return true;
        }
        if ($rental->loadByStreamLink($stream->getId())->status == false) {
            $this->failed("Unable to load rental to transfer");
            return false;
        }
        if ($package->getId() == $rental->getPackageLink()) {
            return true;
        }
        $rental->setPackageLink($package->getId());
        $status = $rental->updateEntry();
        if ($status->status == false) {
            $this->failed("Issue updating rental package:" . $status->message);
        }
        return $status->status;
    }
}
