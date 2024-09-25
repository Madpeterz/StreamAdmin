<?php

namespace App\Endpoint\Control\Client;

use App\Endpoint\Secondlifeapi\Details\Send;
use App\Template\ControlAjax;

class Resend extends ControlAjax
{
    public function process(): void
    {
        global $_POST;
        $_POST["rentalUid"] = $this->siteConfig->getPage();
        $resend = new Send();
        $resend->process();
        $this->output = $resend->getOutputObject();
        $this->createAuditLog($this->siteConfig->getPage(), "resend details");
    }
}
