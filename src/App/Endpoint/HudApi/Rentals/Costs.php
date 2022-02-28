<?php

namespace App\Endpoint\HudApi\Rentals;

use App\Endpoint\SecondLifeApi\Renew\Costandtime;
use App\Template\SecondlifeHudAjax;

class Costs extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->getHudAllowDetails() == false) {
            $this->setSwapTag("message", "Failed - Hud details are currently disabled");
            return;
        }
        if ($this->siteConfig->getSlConfig()->getHudAllowRenewal() == false) {
            $this->setSwapTag("message", "Failed - Hud renewals are currently disabled");
            return;
        }
        $Costandtime = new Costandtime();
        $Costandtime->getCostOfRental($this->Object_OwnerAvatar);
        $this->output = $Costandtime->getOutputObject();
    }
}
