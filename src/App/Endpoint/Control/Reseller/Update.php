<?php

namespace App\Endpoint\Control\Reseller;

use App\Models\Reseller;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    public function process(): void
    {

        $reseller = new Reseller();

        $rate = $this->input->post("rate")->checkInRange(1, 100)->asInt();
        if ($rate === null) {
            $this->failed($this->input->getWhyFailed());
            return;
        }
        $allowed = $this->input->post("allowed")->asBool();
        $this->setSwapTag("redirect", "reseller");
        if ($reseller->loadID($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to load reseller");
            return;
        }
        $oldvalues = $reseller->objectToValueArray();
        $reseller->setRate($rate);
        $reseller->setAllowed($allowed);
        $update_status = $reseller->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf("Unable to update reseller: %1\$s", $update_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Reseller updated");
        $this->createMultiAudit(
            $reseller->getId(),
            $reseller->getFields(),
            $oldvalues,
            $reseller->objectToValueArray()
        );
    }
}
