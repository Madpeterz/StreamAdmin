<?php

namespace App\Endpoint\SecondLifeApi\ProxyRenew;

use App\Endpoint\SecondLifeApi\Renew\Details as RenewDetails;
use App\R7\Model\Avatar;
use App\R7\Set\RentalSet;
use App\R7\Set\StreamSet;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Details extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $targetuid = $input->postFilter("targetuid");
        $avatar = new Avatar();
        $status = false;

        if ($targetuid == null) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }

        $bits = explode(" ", $targetuid);
        $load_status = false;
        if (count($bits) == 2) {
            $firstname = strtolower($bits[0]);
            $firstname = ucfirst($firstname);
            $lastname = strtolower($bits[1]);
            $lastname = ucfirst($lastname);
            $targetuid = "" . $firstname . " " . $lastname . "";
            $load_status = $avatar->loadByField("avatarName", $targetuid);
        } elseif (strlen($targetuid) == 36) {
            $load_status = $avatar->loadByField("avatarUUID", $targetuid);
        }

        $this->setSwapTag("dataset_count", 0);
        if ($load_status == false) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }

        $Details = new RenewDetails();
        $Details->process();
        $this->output = $Details->getOutputObject();
    }
}
