<?php

namespace App\Endpoint\Control\Reseller;

use App\Models\Reseller;
use App\Framework\ViewAjax;

class Update extends ViewAjax
{
    public function process(): void
    {

        $reseller = new Reseller();

        $rate = $this->input->post("rate");
        $allowed = $this->input->post("allowed");
        if ($allowed === null) {
            $allowed = false;
        }
        if ($rate < 1) {
            $this->failed("Rate must be 1 or more (use Allow to disable)");
            return;
        }
        if ($rate > 100) {
            $this->failed("Rate must be 100 or less");
            return;
        }
        $this->setSwapTag("redirect", "reseller");
        if ($reseller->loadID($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to load reseller");
            return;
        }
        $reseller->setRate($rate);
        $reseller->setAllowed($allowed);
        $update_status = $reseller->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed(
                sprintf("Unable to update reseller: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->ok("Reseller updated");
    }
}
