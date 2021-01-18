<?php

namespace App\Endpoints\SecondLifeHudApi\Rentals;

use App\Endpoints\SecondLifeApi\Details\Resend;
use App\Models\Rental;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getdetails extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rental_uid = $input->postFilter("rental_uid");
        $rental = new Rental();
        if ($rental->loadByField("rental_uid", $rental_uid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        if ($rental->getAvatarlink() != $this->object_owner_avatar->getId()) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        $this->setSwapTag("message", "ok but you should never see this message");
        $api_object = new Resend();
        $api_object->process();
        $this->output = $api_object->getOutputObject();
    }
}
