<?php

namespace App\Endpoint\Control\TextureConfig;

use App\Models\Textureconfig;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        $textureconfig = new Textureconfig();

        $name = $this->input->post("name")->checkStringLength(4, 30)->asString();
        if ($name == null) {
            $this->failed("Name failed:" . $this->input->getWhyFailed());
            return;
        }
        $gettingDetails = $this->input->post("gettingDetails")->isUuid()->asString();
        $requestDetails = $this->input->post("requestDetails")->isUuid()->asString();
        $offline = $this->input->post("offline")->isUuid()->asString();
        $waitOwner = $this->input->post("waitOwner")->isUuid()->asString();
        $inUse = $this->input->post("inUse")->isUuid()->asString();
        $makePayment = $this->input->post("makePayment")->isUuid()->asString();
        $stockLevels = $this->input->post("stockLevels")->isUuid()->asString();
        $renewHere = $this->input->post("renewHere")->isUuid()->asString();
        $proxyRenew = $this->input->post("proxyRenew")->isUuid()->asString();
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
        if ($create_status->status == false) {
            $this->failed(
                sprintf("Unable to create Texture pack: %1\$s", $create_status->message)
            );
            return;
        }
        $this->ok("Texture pack created");
        $this->setSwapTag("redirect", "textureconfig");
    }
}
