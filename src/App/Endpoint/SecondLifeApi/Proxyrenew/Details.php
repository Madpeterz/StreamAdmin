<?php

namespace App\Endpoints\SecondLifeApi\Proxyrenew;

use App\Models\Avatar;
use App\Models\RentalSet;
use App\Models\StreamSet;
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
            $load_status = $avatar->loadByField("avatarname", $targetuid);
        } elseif (strlen($targetuid) == 36) {
            $load_status = $avatar->loadByField("avataruuid", $targetuid);
        }

        $this->setSwapTag("dataset_count", 0);
        if ($load_status == false) {
            $this->setSwapTag("message", "Unable to find avatar");
            return;
        }
        $rental_set = new RentalSet();
        $rental_set->loadOnField("avatarlink", $avatar->getId());
        if ($rental_set->getCount() < 1) {
            $this->setSwapTag("message", "Unable to find rentals");
            return;
        }
        $stream_set = new StreamSet();
        $stream_set->loadIds($rental_set->getAllByField("streamlink"));
        if ($stream_set->getCount() < 1) {
            $this->setSwapTag("message", "Unable to find streams attached to rentals!");
            return;
        }
        $reply_dataset = [];
        foreach ($rental_set->getAllIds() as $rental_id) {
            $rental = $rental_set->getObjectByID($rental_id);
            $stream = $stream_set->getObjectByID($rental->getStreamlink());
            if ($stream != null) {
                $reply_dataset[] = "" . $rental->getRental_uid() . "|||" . $stream->getPort() . "";
            }
        }
        if (count($reply_dataset) < 1) {
            $this->setSwapTag("message", "dataset packing has failed in a epic way");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("dataset_count", count($reply_dataset));
        $this->setSwapTag("dataset", $reply_dataset);
        $this->setSwapTag("message", sprintf("Cleint account: %1\$s", $avatar->getAvatarname()));
    }
}
