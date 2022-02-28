<?php

namespace App\Endpoint\HudApi\Details;

use App\Endpoint\SecondLifeApi\Details\Resend as DetailsResend;
use App\Template\SecondlifeHudAjax;

class Resend extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->getHudAllowDetails() == false) {
            $this->setSwapTag("message", "Failed - Hud details are currently disabled");
            return;
        }
        $resend = new DetailsResend();
        $resend->process($this->Object_OwnerAvatar);
        $this->output = $resend->getOutputObject();
    }
}
