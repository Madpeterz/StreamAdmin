<?php

namespace App\Endpoints\SecondLifeHudApi\Rentals;

use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Servertypes;
use App\Models\Stream;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getserverurl extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rental_uid = $input->postFilter("uid");
        $rental = new Rental();
        if ($rental->loadByField("rental_uid", $rental_uid) == true) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        if ($rental->getAvatarlink() != $this->object_owner_avatar->getId()) {
            $this->setSwapTag("message", "System linking error - please try again later");
            return;
        }
        $package = new Package();
        if ($package->loadID($rental->getPackagelink()) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamlink()) == false) {
            $this->setSwapTag("message", "Unable to load stream");
            return;
        }
        $server = new Server();
        if ($server->loadID($stream->getServerlink()) == false) {
            $this->setSwapTag("message", "Unable to load server");
            return;
        }
        $servertypes = new Servertypes();
        if ($servertypes->loadID($package->getServertypelink()) == false) {
            $this->setSwapTag("message", "Unable to load server type");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("serverurl", "http://" . $server->getDomain() . ":" . $stream->getPort());
        $this->setSwapTag("servertype", $servertypes->getName());
    }
}
