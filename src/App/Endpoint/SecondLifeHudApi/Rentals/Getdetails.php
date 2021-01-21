<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

use App\Endpoint\SecondLifeApi\Details\Resend;
use App\Models\Rental;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getdetails extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rentalUid = $input->postFilter("rentalUid");
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $rentalUid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        if ($rental->getAvatarLink() != $this->object_ownerAvatarLinkatar->getId()) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        $this->setSwapTag("message", "ok but you should never see this message");
        $api_object = new Resend();
        $api_object->process();
        $this->output = $api_object->getOutputObject();
    }
}
