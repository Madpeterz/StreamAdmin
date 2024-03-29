<?php

namespace App\Endpoint\SecondLifeApi\Details;

use App\R7\Model\Avatar;
use App\R7\Model\Detail;
use App\R7\Model\Rental;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Resend extends SecondlifeAjax
{
    public function process(?Avatar $forceAv = null): void
    {
        $input = new InputFilter();
        $rentalUid = $input->postFilter("rentalUid");
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $rentalUid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        if ($forceAv != null) {
            if ($rental->getAvatarLink() != $forceAv->getId()) {
                $this->setSwapTag("message", "You can not request someone else's rental details via the hud!");
                return;
            }
        }
        $detail = new Detail();
        $whereConfig = [
            "fields" => ["rentalLink"],
            "values" => [$rental->getId()],
            "types" => ["i"],
            "matches" => ["="],
        ];
        $count_data = $this->sql->basicCountV2($detail->getTable(), $whereConfig);
        if ($count_data["status"] == false) {
            $this->setSwapTag("message", "Unable to check if you have a pending details request");
            return;
        }
        if ($count_data["count"] != 0) {
            $this->ok("You already have a pending details request - please wait for it or contact support.");
            return;
        }
        $detail = new Detail();
        $detail->setRentalLink($rental->getId());
        $create_status = $detail->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag("message", "Unable to create details request");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Details request accepted, it should be with you shortly!");
    }
}
