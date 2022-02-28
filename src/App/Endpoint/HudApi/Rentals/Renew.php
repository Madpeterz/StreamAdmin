<?php

namespace App\Endpoint\HudApi\Rentals;

use App\Endpoint\SecondLifeApi\Renew\Renewnow;
use App\Helpers\ResellerHelper;
use App\Template\SecondlifeHudAjax;

class Renew extends SecondlifeHudAjax
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
        $inputF = new InputFilter();
        $SLtransactionUUID = $inputF->postFilter("transactionUUID", "uuid");
        if ($SLtransactionUUID == null) {
            $this->setSwapTag("message", "Failed - Invaild SL transaction ID");
            return;
        }
        $reseller_helper = new ResellerHelper();
        $get_reseller_status = $reseller_helper->loadOrCreate(
            $this->siteConfig->getSlConfig()->getOwnerAvatarLink(),
            true,
            0
        );
        if ($get_reseller_status == false) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to load reseller");
            return;
        }
        $renew = new Renewnow();
        $renew->setReseller($reseller_helper->getReseller());
        $renew->setRegion($this->region);
        $renew->setOwnerOverride(true);
        $renew->process($this->Object_OwnerAvatar, $SLtransactionUUID);
        $this->output = $renew->getOutputObject();
    }
}
