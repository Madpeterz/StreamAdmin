<?php

namespace App\Endpoints\SecondLifeApi\Details;

use App\Models\Detail;
use App\Models\Rental;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Resend extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rental_uid = $input->postFilter("rental_uid");
        $rental = new Rental();
        if ($rental->loadByField("rental_uid", $rental_uid) == true) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        $detail = new Detail();
        $whereConfig = [
            "fields" => ["rentallink"],
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
            $this->setSwapTag("message", "You already have a pending details request please wait!");
            return;
        }
        $detail = new Detail();
        $detail->setRentallink($rental->getId());
        $create_status = $detail->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag("message", "Unable to create details request");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Details request accepted, it should be with you shortly!");
    }
}
