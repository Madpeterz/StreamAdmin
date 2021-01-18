<?php

namespace App\Endpoints\SecondLifeHudApi\Rentals;

use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Costs extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rental_uid = $input->postFilter("uid");
        $rental = new Rental();
        $this->setSwapTag("message", "unabletoload");
        if ($rental->loadByField("rental_uid", $rental_uid) == false) {
            return;
        }
        if ($rental->getAvatarlink() != $this->object_owner_avatar->getId()) {
            return;
        }
        $package = new Package();
        if ($package->loadID($rental->getPackagelink()) == false) {
            return;
        }
        $avatar_system = new Avatar();
        if ($avatar_system->loadID($this->slconfig->getOwner_av()) == false) {
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("systemowner", $avatar_system->getAvataruuid());
        $this->setSwapTag("cost", $package->getCost());
        $this->setSwapTag("old_expire_time", $rental->getExpireunixtime());
    }
}
