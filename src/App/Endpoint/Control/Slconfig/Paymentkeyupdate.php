<?php

namespace App\Endpoint\Control\Slconfig;

use App\Endpoint\View\Slconfig\Paymentkey;
use App\Template\ControlAjax;

class Paymentkeyupdate extends ControlAjax
{
    public function process(): void
    {
        $key = $this->input->post("assignedkey")->checkStringLength(23, 23)->asString();
        if ($key == null) {
            $this->failed("post key failed checks: " . $this->input->getWhyFailed());
            return;
        }
        $keyCheck = new Paymentkey();
        $results = $keyCheck->getKeyStatus($key, false);
        if ($results->status == false) {
            $this->failed("Key '" . $key . "' failed checks: " . $results->message);
            return;
        }
        if ($key == $this->siteConfig->getSlConfig()->getPaymentKey()) {
            $this->failed("Key not changed");
            return;
        }
        $this->siteConfig->getSlConfig()->setPaymentKey($key);
        $results = $this->siteConfig->getSlConfig()->updateEntry();
        if ($results->status == false) {
            $this->failed("Unable to update key in DB please check and try again");
            return;
        }
        $this->redirectWithMessage("Key updated", "Slconfig/Paymentkey");
        $this->createAuditLog($this->siteConfig->getSlConfig()->getId(), "payment key changed");
    }
}
