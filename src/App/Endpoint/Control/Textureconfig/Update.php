<?php

namespace App\Endpoint\Control\Textureconfig;

use App\Models\Textureconfig;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();

        $name = $input->postFilter("name");
        $gettingDetails = $input->postFilter("gettingDetails", "uuid");
        $requestDetails = $input->postFilter("requestDetails", "uuid");
        $offline = $input->postFilter("offline", "uuid");
        $waitOwner = $input->postFilter("waitOwner", "uuid");
        $inUse = $input->postFilter("inUse", "uuid");
        $makePayment = $input->postFilter("makePayment", "uuid");
        $stockLevels = $input->postFilter("stockLevels", "uuid");
        $renewHere = $input->postFilter("renewHere", "uuid");
        $proxyRenew = $input->postFilter("proxyRenew", "uuid");
        $treevendWaiting = $input->postFilter("treevendWaiting", "uuid");
        if (strlen($name) < 4) {
            $this->setSwapTag("message", "name length must be 4 or more");
            return;
        }
        if (strlen($name) > 30) {
            $this->setSwapTag("message", "name length must be 30 or less");
            return;
        }
        if (strlen($gettingDetails) != 36) {
            $this->setSwapTag("message", "gettingDetails is not a uuid");
            return;
        }
        if (strlen($requestDetails) != 36) {
            $this->setSwapTag("message", "requestDetails is not a uuid");
            return;
        }
        if (strlen($offline) != 36) {
            $this->setSwapTag("message", "offline is not a uuid");
            return;
        }
        if (strlen($waitOwner) != 36) {
            $this->setSwapTag("message", "waitOwner is not a uuid");
            return;
        }
        if (strlen($inUse) != 36) {
            $this->setSwapTag("message", "inUse is not a uuid");
            return;
        }
        if (strlen($makePayment) != 36) {
            $this->setSwapTag("message", "makePayment is not a uuid");
            return;
        }
        if (strlen($stockLevels) != 36) {
            $this->setSwapTag("message", "stockLevels is not a uuid");
            return;
        }
        if (strlen($renewHere) != 36) {
            $this->setSwapTag("message", "renewHereis not a uuid");
            return;
        }
        if (strlen($proxyRenew) != 36) {
            $this->setSwapTag("message", "proxyRenew not a uuid");
            return;
        }
        if (strlen($treevendWaiting) != 36) {
            $this->setSwapTag("message", "treevendWaiting is not a uuid");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to load texture pack");
            $this->setSwapTag("redirect", "textureconfig");
            return;
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
        $textureconfig->setTreevendWaiting($treevendWaiting);
        $update_status = $textureconfig->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag("message", "Texture pack updated");
            $this->setSwapTag("redirect", "textureconfig");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag(
            "message",
            sprintf("Unable to update Texture pack: %1\$s", $update_status["message"])
        );
    }
}
