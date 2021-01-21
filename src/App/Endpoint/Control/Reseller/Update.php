<?php

namespace App\Endpoint\Control\Reseller;

use App\Models\Reseller;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $reseller = new Reseller();

        $rate = $input->postFilter("rate", "integer");
        $allowed = $input->postFilter("allowed", "bool");
        if ($rate < 1) {
            $this->setSwapTag("message", "Rate must be 1 or more (use Allow to disable)");
            return;
        }
        if ($rate > 100) {
            $this->setSwapTag("message", "Rate must be 100 or less");
            return;
        }
        $this->setSwapTag("redirect", "reseller");
        if ($reseller->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to load reseller");
            return;
        }
        $reseller->setRate($rate);
        $reseller->setAllowed($allowed);
        $update_status = $reseller->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update reseller: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Reseller updated");
    }
}
