<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Servertypes;
use App\R7\Model\Stream;
use App\Template\SecondlifeHudAjax;
use YAPF\InputFilter\InputFilter;

class GetServerURL extends SecondlifeHudAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rentalUid = $input->postFilter("rentalUid");
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $rentalUid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        if ($rental->getAvatarLink() != $this->Object_OwnerAvatar->getId()) {
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
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("serverurl", "http://" . $server->getDomain() . ":" . $stream->getPort());
        $this->setSwapTag("servertype", $servertypes->getName());
    }
}
