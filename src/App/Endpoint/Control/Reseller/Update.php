<?php

namespace App\Endpoint\Control\Reseller;

use App\R7\Model\Reseller;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $reseller = new Reseller();

        $rate = $input->postInteger("rate");
        $allowed = $input->postBool("allowed");
        if ($rate < 1) {
            $this->failed("Rate must be 1 or more (use Allow to disable)");
            return;
        }
        if ($rate > 100) {
            $this->failed("Rate must be 100 or less");
            return;
        }
        $this->setSwapTag("redirect", "reseller");
        if ($reseller->loadID($this->page) == false) {
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
