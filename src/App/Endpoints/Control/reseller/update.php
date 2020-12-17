<?php

namespace App\Endpoints\Control\Reseller;

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
            $this->output->setSwapTagString("message", "Rate must be 1 or more (use Allow to disable)");
            return;
        }
        if ($rate > 100) {
            $this->output->setSwapTagString("message", "Rate must be 100 or less");
            return;
        }
        $this->output->setSwapTagString("redirect", "reseller");
        if ($reseller->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to load reseller");
            return;
        }
        $reseller->setRate($rate);
        $reseller->setAllowed($allowed);
        $update_status = $reseller->updateEntry();
        if ($update_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to update reseller: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Reseller updated");
    }
}
