<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

use App\Models\Rental;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getserverstatus extends SecondlifeAjax
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
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("timeleft", "Expired: " . expired_ago($rental->getExpireUnixtime()));
        $this->setSwapTag("expires", "Expired: " . date('l jS \of F Y h:i:s A', $rental->getExpireUnixtime()));
        if ($rental->getExpireUnixtime() > time()) {
            $this->setSwapTag("timeleft", "Timeleft: " . timeleft_hours_and_days($rental->getExpireUnixtime()));
            $this->setSwapTag(
                "expires",
                "Renewal due by: " . date('l jS \of F Y h:i:s A', $rental->getExpireUnixtime())
            );
        }
    }
}
