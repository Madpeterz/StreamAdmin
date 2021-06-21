<?php

namespace App\Endpoint\HudApi\Rentals;

use App\Endpoint\SecondLifeApi\Renew\Renewnow;
use App\Template\SecondlifeHudAjax;
use YAPF\InputFilter\InputFilter;

class Renew extends SecondlifeHudAjax
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
        $inputF = new InputFilter();
        $SLtransactionUUID = $inputF->postFilter("transactionUUID", "uuid");
        if ($SLtransactionUUID == null) {
            $this->setSwapTag("message", "Failed - Invaild SL transaction ID");
            return;
        }
        $renew = new Renewnow();
        $renew->process($this->Object_OwnerAvatar, $SLtransactionUUID);
    }
}
