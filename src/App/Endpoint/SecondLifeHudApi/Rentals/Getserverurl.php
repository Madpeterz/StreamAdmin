<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

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
        $rentalUid = $input->postFilter("uid");
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $rentalUid) == true) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        if ($rental->getAvatarLink() != $this->object_ownerAvatarLinkatar->getId()) {
            $this->setSwapTag("message", "System linking error - please try again later");
            return;
        }
        $package = new Package();
        if ($package->loadID($rental->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to load stream");
            return;
        }
        $server = new Server();
        if ($server->loadID($stream->getServerLink()) == false) {
            $this->setSwapTag("message", "Unable to load server");
            return;
        }
        $servertypes = new Servertypes();
        if ($servertypes->loadID($package->getServertypeLink()) == false) {
            $this->setSwapTag("message", "Unable to load server type");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("serverurl", "http://" . $server->getDomain() . ":" . $stream->getPort());
        $this->setSwapTag("servertype", $servertypes->getName());
    }
}
