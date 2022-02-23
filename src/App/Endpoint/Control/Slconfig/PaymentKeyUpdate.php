<?php

namespace App\Endpoint\Control\Slconfig;

use App\Endpoint\View\Slconfig\PaymentKey;
use App\Framework\ViewAjax;

class PaymentKeyUpdate extends ViewAjax
{
    public function process(): void
    {

        $key = $input->postString("assignedkey", 23, 23);
        $keyCheck = new PaymentKey();
        $results = $keyCheck->getKeyStatus($key, false);
        if ($results != "ok") {
            $this->failed("Key failed checks: " . $results);
            return;
        }
        if ($key == $this->slconfig->getPaymentKey()) {
            $this->failed("Key not changed");
            return;
        }
        $this->slconfig->setPaymentKey($key);
        $results = $this->slconfig->updateEntry();
        if ($results["status"] == false) {
            $this->failed("Unable to update key in DB please check and try again");
            return;
        }
        $this->ok("Key updated");
        $this->setSwapTag("redirect", "Slconfig/PaymentKey");
    }
}
