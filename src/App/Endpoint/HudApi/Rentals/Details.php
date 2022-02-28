<?php

namespace App\Endpoint\HudApi\Rentals;

use App\Endpoint\SecondLifeApi\Renew\Details as RenewDetails;
use App\Template\SecondlifeHudAjax;

class Details extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->getHudAllowDetails() == false) {
            $this->setSwapTag("message", "Failed - Hud details are currently disabled");
            return;
        }
        $Details = new RenewDetails();
        $Details->getRentalDetailsForAvatar($this->Object_OwnerAvatar);
        $this->output = $Details->getOutputObject();
    }
}
