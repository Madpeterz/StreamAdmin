<?php

namespace App\Endpoint\SecondLifeApi\Renew;

use App\R7\Model\Avatar;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Stream;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Costandtime extends SecondlifeAjax
{
    public function getCostOfRental(?Avatar $forceCheckAv): void
    {
        $inputF = new InputFilter();
        $rentalUid = $inputF->postString("rentalUid");
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $rentalUid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to find stream");
            return;
        }
        $package = new Package();
        if ($package->loadID($stream->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        if ($forceCheckAv != null) {
            if ($forceCheckAv->getId() != $rental->getAvatarLink()) {
                $this->setSwapTag("message", "Unable to renew someone elses rental via your hud, 
                please use the proxy pay!");
                return;
            }
        }
        $masterAvatar = new Avatar();
        if ($masterAvatar->loadID($this->slconfig->getOwnerAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load system owner");
            return;
        }
        $this->setSwapTag("systemowner", $masterAvatar->getAvatarUUID());
        $this->setSwapTag("status", true);
        $this->setSwapTag("cost", $package->getCost());
        $this->setSwapTag("message", timeleftHoursAndDays($rental->getExpireUnixtime()));
    }
    public function process(): void
    {
        $this->getCostOfRental(null);
    }
}
