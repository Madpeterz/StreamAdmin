<?php

namespace App\Endpoints\SecondLifeApi\Texturepack;

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
        $this->setSwapTag("status", "true");
        $this->setSwapTag("texture_offline", $textureconfig->getOffline());
        $this->setSwapTag("texture_waitingforowner", $textureconfig->getWait_owner());
        $this->setSwapTag("texture_fetchingdetails", $textureconfig->getGetting_details());
        $this->setSwapTag("texture_request_payment", $textureconfig->getMake_payment());
        $this->setSwapTag("texture_renewhere", $textureconfig->getRenew_here());
        $this->setSwapTag("texture_inuse", $textureconfig->getInuse());
        $this->setSwapTag("texture_request_details", $textureconfig->getRequest_details());
        $this->setSwapTag("texture_stock_levels", $textureconfig->getStock_levels());
        $this->setSwapTag("texture_proxyrenew", $textureconfig->getProxyrenew());
        $this->setSwapTag("texture_treevend_waiting", $textureconfig->getTreevend_waiting());


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
