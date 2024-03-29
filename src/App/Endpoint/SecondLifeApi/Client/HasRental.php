<?php

namespace App\Endpoint\SecondLifeApi\Client;

use App\R7\Model\Avatar;
use App\R7\Set\RentalSet;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class HasRental extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $avUUID = $input->postUUID("checkinguuid");
        if ($avUUID === null) {
            $this->ok("No UUID");
            return;
        }
        $avatar = new Avatar();
        $avatar->loadByAvatarUUID($avUUID);
        if ($avatar->isLoaded() == false) {
            $this->ok("No avatar");
            return;
        }
        $rentalSet = new RentalSet();
        $whereConfig = [
            "fields" => ["avatarLink"],
            "values" => [$avatar->getId()],
        ];
        $count = $rentalSet->countInDB($whereConfig);
        if (($count === null) || ($count == 0)) {
            $this->ok("No streams");
            return;
        }
        $this->ok("some");
        return;
    }
}
