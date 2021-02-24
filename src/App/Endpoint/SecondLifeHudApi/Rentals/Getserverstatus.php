<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

use App\R7\Model\Rental;
use App\Template\SecondlifeHudAjax;
use YAPF\InputFilter\InputFilter;

class GetServerStatus extends SecondlifeHudAjax
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
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
        $this->setSwapTag("timeleft", "Expired: " . expiredAgo($rental->getExpireUnixtime()));
        $this->setSwapTag("expires", "Expired: " . date('l jS \of F Y h:i:s A', $rental->getExpireUnixtime()));
        if ($rental->getExpireUnixtime() > time()) {
            $this->setSwapTag("timeleft", "Timeleft: " . timeleftHoursAndDays($rental->getExpireUnixtime()));
            $this->setSwapTag(
                "expires",
                "Renewal due by: " . date('l jS \of F Y h:i:s A', $rental->getExpireUnixtime())
            );
        }
    }
}
