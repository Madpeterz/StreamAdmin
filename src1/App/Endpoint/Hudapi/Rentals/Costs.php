<?php

namespace App\Endpoint\Hudapi\Rentals;

use App\Endpoint\Secondlifeapi\Renew\CostAndTime;
use App\Template\SecondlifeHudAjax;

class Costs extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->getHudAllowDetails() == false) {
            $this->failed("Hud details are currently disabled");
            return;
        }
        if ($this->siteConfig->getSlConfig()->getHudAllowRenewal() == false) {
            $this->failed("Hud renewals are currently disabled");
            return;
        }
        $Costandtime = new CostAndTime();
        $Costandtime->getCostOfRental($this->Object_OwnerAvatar);
        $this->output = $Costandtime->getOutputObject();
    }
}
