<?php

namespace App\Endpoint\Control\Textureconfig;

use App\Models\Textureconfig;
use App\Framework\ViewAjax;

class Create extends ViewAjax
{
    public function process(): void
    {

        $textureconfig = new Textureconfig();

        $name = $this->input->post("name", 30, 4);
        if ($name == null) {
            $this->failed("Name failed:" . $this->input->getWhyFailed());
            return;
        }
        $gettingDetails = $this->input->post("gettingDetails");
        $requestDetails = $this->input->post("requestDetails");
        $offline = $this->input->post("offline");
        $waitOwner = $this->input->post("waitOwner");
        $inUse = $this->input->post("inUse");
        $makePayment = $this->input->post("makePayment");
        $stockLevels = $this->input->post("stockLevels");
        $renewHere = $this->input->post("renewHere");
        $proxyRenew = $this->input->post("proxyRenew");
        $testing = [
            "gettingDetail" => $gettingDetails,
            "requestDetails" => $requestDetails,
            "offline" => $offline,
            "waitOwner" => $waitOwner,
            "inUse" => $inUse,
            "makePayment" => $makePayment,
            "stockLevels" => $stockLevels,
            "renewHere" => $renewHere,
            "proxyRenew" => $proxyRenew,
        ];
        $testing = array_reverse($testing, true);
        foreach ($testing as $key => $value) {
            if ($value == null) {
                $this->failed("Entry: " . $key . " is not set - " . $this->input->getWhyFailed());
                return;
            }
        }
        $textureconfig->setName($name);
        $textureconfig->setOffline($offline);
        $textureconfig->setWaitOwner($waitOwner);
        $textureconfig->setStockLevels($stockLevels);
        $textureconfig->setMakePayment($makePayment);
        $textureconfig->setInUse($inUse);
        $textureconfig->setRenewHere($renewHere);
        $textureconfig->setGettingDetails($gettingDetails);
        $textureconfig->setRequestDetails($requestDetails);
        $textureconfig->setProxyRenew($proxyRenew);
        $create_status = $textureconfig->createEntry();
        if ($create_status["status"] == false) {
            $this->failed(
                sprintf("Unable to create Texture pack: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->ok("Texture pack created");
        $this->setSwapTag("redirect", "textureconfig");
    }
}
