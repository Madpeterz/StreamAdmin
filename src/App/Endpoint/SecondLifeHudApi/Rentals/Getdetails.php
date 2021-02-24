<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

use App\Endpoint\SecondLifeApi\Details\Resend;
use App\R7\Model\Rental;
use App\Template\SecondlifeHudAjax;
use YAPF\InputFilter\InputFilter;

class GetDetails extends SecondlifeHudAjax
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
        if ($rental->getAvatarLink() != $this->Object_OwnerAvatar->getId()) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        $this->setSwapTag("message", "ok");
        $api_object = new Resend();
        $api_object->process();
        $this->output = $api_object->getOutputObject();
    }
}
