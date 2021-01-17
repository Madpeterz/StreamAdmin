<?php

namespace App\Endpoints\Control\Textureconfig;

use App\Models\Textureconfig;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();

        $name = $input->postFilter("name");
        $getting_details = $input->postFilter("getting_details", "uuid");
        $request_details = $input->postFilter("request_details", "uuid");
        $offline = $input->postFilter("offline", "uuid");
        $wait_owner = $input->postFilter("wait_owner", "uuid");
        $inuse = $input->postFilter("inuse", "uuid");
        $make_payment = $input->postFilter("make_payment", "uuid");
        $stock_levels = $input->postFilter("stock_levels", "uuid");
        $renew_here = $input->postFilter("renew_here", "uuid");
        $proxyrenew = $input->postFilter("proxyrenew", "uuid");
        $treevend_waiting = $input->postFilter("treevend_waiting", "uuid");
        if (strlen($name) < 4) {
            $this->setSwapTag("message", "name length must be 4 or more");
            return;
        }
        if (strlen($name) > 30) {
            $this->setSwapTag("message", "name length must be 30 or less");
            return;
        }
        if (strlen($getting_details) != 36) {
            $this->setSwapTag("message", "getting_details is not a uuid");
            return;
        }
        if (strlen($request_details) != 36) {
            $this->setSwapTag("message", "request_details is not a uuid");
            return;
        }
        if (strlen($offline) != 36) {
            $this->setSwapTag("message", "offline is not a uuid");
            return;
        }
        if (strlen($wait_owner) != 36) {
            $this->setSwapTag("message", "wait_owner is not a uuid");
            return;
        }
        if (strlen($inuse) != 36) {
            $this->setSwapTag("message", "inuse is not a uuid");
            return;
        }
        if (strlen($make_payment) != 36) {
            $this->setSwapTag("message", "make_payment is not a uuid");
            return;
        }
        if (strlen($stock_levels) != 36) {
            $this->setSwapTag("message", "stock_levels is not a uuid");
            return;
        }
        if (strlen($renew_here) != 36) {
            $this->setSwapTag("message", "renew_hereis not a uuid");
            return;
        }
        if (strlen($proxyrenew) != 36) {
            $this->setSwapTag("message", "proxyrenew not a uuid");
            return;
        }
        if (strlen($treevend_waiting) != 36) {
            $this->setSwapTag("message", "treevend_waiting is not a uuid");
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
        $textureconfig->setWait_owner($wait_owner);
        $textureconfig->setStock_levels($stock_levels);
        $textureconfig->setMake_payment($make_payment);
        $textureconfig->setInuse($inuse);
        $textureconfig->setRenew_here($renew_here);
        $textureconfig->setGetting_details($getting_details);
        $textureconfig->setRequest_details($request_details);
        $textureconfig->setProxyrenew($proxyrenew);
        $textureconfig->setTreevend_waiting($treevend_waiting);
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
