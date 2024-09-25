<?php

namespace App\Endpoint\Hudapi\Rentals;

use App\Endpoint\Secondlifeapi\Renew\Details as RenewDetails;
use App\Template\SecondlifeHudAjax;

class Details extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->getHudAllowDetails() == false) {
            $this->failed("Hud details are currently disabled");
            return;
        }
        $Details = new RenewDetails();
        $Details->getRentalDetailsForAvatar($this->Object_OwnerAvatar);
        $this->output = $Details->getOutputObject();
    }
}
