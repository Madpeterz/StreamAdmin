<?php

namespace App\Endpoint\Control\Textureconfig;

use App\R7\Model\Textureconfig;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $textureconfig = new Textureconfig();

        $name = $input->postString("name", 30, 4);
        if ($name == null) {
            $this->failed("Name failed:" . $input->getWhyFailed());
            return;
        }
        $gettingDetails = $input->postUUID("gettingDetails");
        $requestDetails = $input->postUUID("requestDetails");
        $offline = $input->postUUID("offline");
        $waitOwner = $input->postUUID("waitOwner");
        $inUse = $input->postUUID("inUse");
        $makePayment = $input->postUUID("makePayment");
        $stockLevels = $input->postUUID("stockLevels");
        $renewHere = $input->postUUID("renewHere");
        $proxyRenew = $input->postUUID("proxyRenew");
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
                $this->failed("Entry: " . $key . " is not set - " . $input->getWhyFailed());
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
