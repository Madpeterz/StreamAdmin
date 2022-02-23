<?php

namespace App\Endpoint\SecondLifeApi\ProxyRenew;

use App\Endpoint\SecondLifeApi\Renew\Details as RenewDetails;
use App\Models\Avatar;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;
use App\Template\SecondlifeAjax;

class Details extends SecondlifeAjax
{
    public function process(): void
    {

        $targetuid = $input->postFilter("targetuid");
        $avatar = new Avatar();

        if ($targetuid == null) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }

        $bits = explode(" ", $targetuid);
        $load_status = false;
        $matchWith = "None";
        if (count($bits) == 2) {
            $matchWith = "Name";
            $firstname = $bits[0];
            $lastname = $bits[1];
            $targetuid = "" . $firstname . " " . $lastname . "";
            $load_status = $avatar->loadByField("avatarName", $targetuid);
        } elseif (strlen($targetuid) == 36) {
            $matchWith = "UUID";
            $load_status = $avatar->loadByField("avatarUUID", $targetuid);
        } else {
            $this->setSwapTag("message", "UUID or Firstname Lastname must be given");
            return;
        }

        $this->setSwapTag("dataset_count", 0);
        if ($load_status == false) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "Unable to find avatar! attempted to match with: " . $matchWith);
            return;
        }
        $Details = new RenewDetails();
        $Details->getRentalDetailsForAvatar($avatar);
        $this->output = $Details->getOutputObject();
    }
}
