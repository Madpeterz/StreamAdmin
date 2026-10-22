<?php

namespace App\Endpoint\Hudapi\Rentals;

use App\Endpoint\Secondlifeapi\Renew\Costandtime;
use App\Template\SecondlifeHudAjax;

class Costs extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->_HudAllowDetails == false) {
            $this->failed("Hud details are currently disabled");
            return;
        }
        if ($this->siteConfig->getSlConfig()->_HudAllowRenewal == false) {
            $this->failed("Hud renewals are currently disabled");
            return;
        }
        $Costandtime = new Costandtime();
        $Costandtime->getCostOfRental($this->Object_OwnerAvatar);
        $this->output = $Costandtime->getOutputObject();
    }
}
