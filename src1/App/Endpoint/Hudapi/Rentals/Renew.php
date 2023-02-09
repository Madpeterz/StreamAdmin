<?php

namespace App\Endpoint\Hudapi\Rentals;

use App\Endpoint\Secondlifeapi\Renew\Renewnow;
use App\Helpers\ResellerHelper;
use App\Template\SecondlifeHudAjax;

class Renew extends SecondlifeHudAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->getHudAllowDetails() == false) {
            $this->failed("Hud details are currently disabled");
            return;
        }
        if ($this->siteConfig->getSlConfig()->getHudAllowRenewal() == false) {
            $this->failed("Hud renewals are currently disabled");
            return;
        }
        $SLtransactionUUID = $this->input->post("transactionUUID")->isUuid()->asString();
        if ($SLtransactionUUID == null) {
            $this->failed("Invaild SL transaction ID");
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
            $this->failed("Unable to load reseller");
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
