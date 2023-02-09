<?php

namespace App\Endpoint\Hudapi\Details;

use App\Endpoint\Secondlifeapi\Details\Send;
use App\Template\SecondlifeHudAjax;

class Resend extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->getHudAllowDetails() == false) {
            $this->failed("Hud details are currently disabled");
            return;
        }
        $resend = new Send();
        $resend->process($this->Object_OwnerAvatar);
        $this->output = $resend->getOutputObject();
    }
}
