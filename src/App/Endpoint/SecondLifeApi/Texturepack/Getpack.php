<?php

namespace App\Endpoint\SecondLifeApi\Texturepack;

use App\R7\Model\Textureconfig;
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
        $this->setSwapTag("Texture-Offline", $textureconfig->getOffline());
        $this->setSwapTag("Texture-WaitOwner", $textureconfig->getWaitOwner());
        $this->setSwapTag("Texture-GettingDetails", $textureconfig->getGettingDetails());
        $this->setSwapTag("Texture-MakePayment", $textureconfig->getMakePayment());
        $this->setSwapTag("Texture-RenewHere", $textureconfig->getRenewHere());
        $this->setSwapTag("Texture-InUse", $textureconfig->getInUse());
        $this->setSwapTag("Texture-RequestDetails", $textureconfig->getRequestDetails());
        $this->setSwapTag("Texture-StockLevels", $textureconfig->getStockLevels());
        $this->setSwapTag("Texture-ProxyRenew", $textureconfig->getProxyRenew());
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
