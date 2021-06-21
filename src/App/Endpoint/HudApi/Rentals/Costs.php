<?php

namespace App\Endpoint\HudApi\Config;

use App\Endpoint\SecondLifeApi\Renew\Costandtime;
use App\Template\SecondlifeHudAjax;

class Costs extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->slconfig->getHudAllowDetails() == false) {
            $this->setSwapTag("message", "Failed - Hud details are currently disabled");
            return;
        }
        if ($this->slconfig->getHudAllowRenewal() == false) {
            $this->setSwapTag("message", "Failed - Hud renewals are currently disabled");
            return;
        }
        $Costandtime = new Costandtime();
        $Costandtime->getCostOfRental($this->Object_OwnerAvatar);
        $this->output = $Costandtime->getOutputObject();
    }
}
