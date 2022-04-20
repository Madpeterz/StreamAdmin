<?php

namespace App\Endpoint\SecondLifeApi\Details;

use App\Models\Avatar;
use App\Models\Detail;
use App\Models\Rental;
use App\Template\SecondlifeAjax;

class Send extends SecondlifeAjax
{
    public function process(?Avatar $forceAv = null): void
    {
        $rentalUid = $this->input->post("rentalUid")->asString();
        $rental = new Rental();
        $rental->loadByField("rentalUid", $rentalUid);
        if ($rental->isLoaded() == false) {
            $this->failed("Unable to find rental");
            return;
        }
        if ($forceAv != null) {
            if ($rental->getAvatarLink() != $forceAv->getId()) {
                $this->failed("You can not request someone else's rental details via the hud!");
                return;
            }
        }
        $detail = new Detail();
        $load = $detail->loadByRentalLink($rental->getId());
        if ($load->status == false) {
            $this->failed("Unable to check if you have a pending details request");
            return;
        }
        if ($detail->isLoaded() == true) {
            $this->ok("You already have a pending details request - please wait for it or contact support.");
            return;
        }
        $detail = new Detail();
        $detail->setRentalLink($rental->getId());
        $create_status = $detail->createEntry();
        if ($create_status["status"] == false) {
            $this->failed("Unable to create details request");
            return;
        }
        $this->ok("Details request accepted, it should be with you shortly!");
    }
}
