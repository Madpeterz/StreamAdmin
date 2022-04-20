<?php

namespace App\Endpoint\SecondLifeApi\Client;

use App\Models\Avatar;
use App\Models\Sets\RentalSet;
use App\Template\SecondlifeAjax;

class HasRental extends SecondlifeAjax
{
    public function process(): void
    {
        $avUUID = $this->input->post("checkinguuid")->isUuid()->asString();
        if ($avUUID === null) {
            $this->ok("No UUID");
            return;
        }
        $avatar = new Avatar();
        $avatar->loadByAvatarUUID($avUUID);
        if ($avatar->isLoaded() == false) {
            $this->ok("Unknown user");
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
