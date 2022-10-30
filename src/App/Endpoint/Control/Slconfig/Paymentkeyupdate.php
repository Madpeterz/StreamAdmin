<?php

namespace App\Endpoint\Control\Slconfig;

use App\Endpoint\View\Slconfig\PaymentKey;
use App\Template\ControlAjax;

class Paymentkeyupdate extends ControlAjax
{
    public function process(): void
    {
        $key = $this->input->post("assignedkey")->checkStringLength(23, 23)->asString();
        $keyCheck = new PaymentKey();
        $results = $keyCheck->getKeyStatus($key, false);
        if ($results->status != true) {
            $this->failed("Key failed checks: " . $results->message);
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
    }
}
