<?php

namespace App\Endpoint\SecondLifeApi\Texturepack;

use App\Models\Textureconfig;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getpack extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $texturepack = $input->postFilter("texturepack", "integer");
        if ($texturepack < 1) {
            $this->setSwapTag("message", "Invaild texturepack id (or non sent)");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($texturepack) == false) {
            $this->setSwapTag("message", "Unable to load texture pack");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("texture_offline", $textureconfig->getOffline());
        $this->setSwapTag("texture_waitingforowner", $textureconfig->getWaitOwner());
        $this->setSwapTag("texture_fetchingdetails", $textureconfig->getGettingDetails());
        $this->setSwapTag("texture_request_payment", $textureconfig->getMakePayment());
        $this->setSwapTag("texture_renewhere", $textureconfig->getRenewHere());
        $this->setSwapTag("texture_inUse", $textureconfig->getInUse());
        $this->setSwapTag("texture_requestDetails", $textureconfig->getRequestDetails());
        $this->setSwapTag("texture_stockLevels", $textureconfig->getStockLevels());
        $this->setSwapTag("texture_proxyRenew", $textureconfig->getProxyRenew());
        $this->setSwapTag("texture_treevendWaiting", $textureconfig->getTreevendWaiting());


        $this->setSwapTag("reseller_rate", 100);
        $this->setSwapTag("reseller_mode", "Owner mode");
        $this->setSwapTag("message", "ok");
        // reseller config (send anyway even if not wanted)
        if ($this->owner_override == false) {
            $this->setSwapTag("reseller_rate", $this->reseller->getRate());
            $this->setSwapTag("reseller_mode", "Reseller mode");
        }
    }
}
