<?php

namespace App\Endpoints\SecondLifeHudApi\Rentals;

use App\Models\Rental;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getserverstatus extends SecondlifeAjax
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
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("timeleft", "Expired: " . expired_ago($rental->getExpireunixtime()));
        $this->setSwapTag("expires", "Expired: " . date('l jS \of F Y h:i:s A', $rental->getExpireunixtime()));
        if ($rental->getExpireunixtime() > time()) {
            $this->setSwapTag("timeleft", "Timeleft: " . timeleft_hours_and_days($rental->getExpireunixtime()));
            $this->setSwapTag(
                "expires",
                "Renewal due by: " . date('l jS \of F Y h:i:s A', $rental->getExpireunixtime())
            );
        }
    }
}
