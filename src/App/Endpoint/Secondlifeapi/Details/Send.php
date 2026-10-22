<?php

namespace App\Endpoint\Secondlifeapi\Details;

use App\Models\Avatar;
use App\Models\Detail;
use App\Models\Rental;
use App\Models\Set\DetailSet;
use App\Template\SecondlifeAjax;

class Send extends SecondlifeAjax
{
    public function process(?Avatar $forceAv = null): void
    {
        $rentalUid = $this->input->post("rentalUid")->asString();
        if ($rentalUid == null) {
            $this->failed("No rental requested please check your API docs");
            return;
        }
        $rental = new Rental();
        $rental->loadByRentalUid($rentalUid);
        if ($rental->isLoaded() == false) {
            $this->failed("Unable to find rental");
            return;
        }
        if ($forceAv != null) {
            if ($rental->_AvatarLink != $forceAv->_Id) {
                $this->failed("You can not request someone else's rental details via the hud!");
                return;
            }
        }

        $whereConfig = [
            "fields" => ["rentalLink"],
            "values" => [$rental->_Id],
        ];
        $detail = new DetailSet();
        $load = $detail->countInDB($whereConfig);
        if ($load->status == false) {
            $this->failed("Unable to check if you have a pending details request");
            return;
        }
        if ($load->items > 0) {
            $this->ok("You already have a pending details request - please wait for it or contact support.");
            return;
        }
        $detail = new Detail();
        $detail->_RentalLink = $rental->_Id;
        $create_status = $detail->createEntry();
        if ($create_status->status == false) {
            $this->failed("Unable to create details request");
            return;
        }
        $this->ok("Details request accepted, it should be with you shortly!");
    }
}
