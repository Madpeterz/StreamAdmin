<?php

namespace App\Endpoint\Secondlifeapi\Details;

use App\Models\Avatar;
use App\Models\Detail;
use App\Models\Rental;
use App\Models\Sets\DetailSet;
use App\Template\SecondlifeAjax;

class Send extends SecondlifeAjax
{
    public function process(?Avatar $forceAv = null): void
    {
        $rentalUid = $this->input->post("rentalUid")->asString();
        $rental = new Rental();
        $rental->loadByRentalUid($rentalUid);
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

        $whereConfig = [
            "fields" => ["rentalLink"],
            "values" => [$rental->getId()],
        ];
        $detail = new DetailSet();
        $load = $detail->countInDB($whereConfig);
        if ($load === null) {
            $this->failed("Unable to check if you have a pending details request");
            return;
        }
        if ($load > 0) {
            $this->ok("You already have a pending details request - please wait for it or contact support.");
            return;
        }
        $detail = new Detail();
        $detail->setRentalLink($rental->getId());
        $create_status = $detail->createEntry();
        if ($create_status->status == false) {
            $this->failed("Unable to create details request");
            return;
        }
        $this->ok("Details request accepted, it should be with you shortly!");
    }
}
