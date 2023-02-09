<?php

namespace App\Endpoint\Secondlifeapi\Proxyrenew;

use App\Endpoint\Secondlifeapi\Renew\Details as RenewDetails;
use App\Models\Avatar;
use App\Template\SecondlifeAjax;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply;

class Details extends SecondlifeAjax
{
    public function process(): void
    {
        $this->setSwapTag("dataset_count", 0);
        $targetuid = $this->input->post("targetuid")->checkStringLengthMin(3)->asString();
        $avatar = new Avatar();
        if ($targetuid == null) {
            $this->failed("Unable to find avatar");
            return;
        }

        $bits = explode(" ", $targetuid);
        $load_status = new SingleLoadReply("not processed");
        if (count($bits) == 2) {
            $firstname = $bits[0];
            $lastname = $bits[1];
            $targetuid = "" . $firstname . " " . $lastname . "";
            $load_status = $avatar->loadByAvatarName($targetuid);
        } elseif (nullSafeStrLen($targetuid) == 36) {
            $load_status = $avatar->loadByAvatarUUID($targetuid);
        }
        if ($load_status->status == false) {
            $this->failed("Unable to find avatar!");
            return;
        }

        $Details = new RenewDetails();
        $Details->getRentalDetailsForAvatar($avatar);
        $this->output = $Details->getOutputObject();
    }
}
