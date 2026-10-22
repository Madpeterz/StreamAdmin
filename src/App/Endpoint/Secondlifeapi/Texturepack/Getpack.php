<?php

namespace App\Endpoint\Secondlifeapi\Texturepack;

use App\Models\Textureconfig;
use App\Template\SecondlifeAjax;

class Getpack extends SecondlifeAjax
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
        $this->setSwapTag("Texture-Offline", $textureconfig->_Offline);
        $this->setSwapTag("Texture-WaitOwner", $textureconfig->_WaitOwner);
        $this->setSwapTag("Texture-GettingDetails", $textureconfig->_GettingDetails);
        $this->setSwapTag("Texture-MakePayment", $textureconfig->_MakePayment);
        $this->setSwapTag("Texture-RenewHere", $textureconfig->_RenewHere);
        $this->setSwapTag("Texture-InUse", $textureconfig->_InUse);
        $this->setSwapTag("Texture-RequestDetails", $textureconfig->_RequestDetails);
        $this->setSwapTag("Texture-StockLevels", $textureconfig->_StockLevels);
        $this->setSwapTag("Texture-ProxyRenew", $textureconfig->_ProxyRenew);
        $this->setSwapTag("reseller_rate", 100);
        $this->setSwapTag("reseller_mode", "Owner mode");
        $this->ok("ok");
        // reseller config (send anyway even if not wanted)
        if ($this->owner_override == false) {
            $this->setSwapTag("reseller_rate", $this->reseller->_Rate);
            $this->setSwapTag("reseller_mode", "Reseller mode");
        }
    }
}
