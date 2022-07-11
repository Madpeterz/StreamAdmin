<?php

namespace App\Endpoint\SecondLifeApi\Texturepack;

use App\Models\Textureconfig;
use App\Template\SecondlifeAjax;

class GetPack extends SecondlifeAjax
{
    public function process(): void
    {
        $texturepack = $this->input->post("texturepack")->asInt();
        if ($texturepack < 1) {
            $this->failed("Invaild texturepack id (or non sent)");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($texturepack)->status == false) {
            $this->failed("Unable to load texture pack");
            return;
        }
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
        $this->ok("ok");
        // reseller config (send anyway even if not wanted)
        if ($this->owner_override == false) {
            $this->setSwapTag("reseller_rate", $this->reseller->getRate());
            $this->setSwapTag("reseller_mode", "Reseller mode");
        }
    }
}
