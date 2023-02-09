<?php

namespace App\Endpoint\Secondlifeapi\Object;

use App\Template\SecondlifeAjax;

class Severrequestcode extends SecondlifeAjax
{
    public function process(): void
    {
        if ($this->owner_override == false) {
            $this->failed("SystemAPI access only - please contact support");
            return;
        }
        $this->ok(explode("*", $this->siteConfig->getSlConfig()->getPaymentKey())[0]);
    }
}
