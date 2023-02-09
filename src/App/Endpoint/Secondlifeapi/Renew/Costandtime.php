<?php

namespace App\Endpoint\Secondlifeapi\Renew;

use App\Models\Avatar;
use App\Models\Rental;
use App\Template\SecondlifeAjax;

class Costandtime extends SecondlifeAjax
{
    public function getCostOfRental(?Avatar $forceCheckAv): void
    {
        $rentalUid = $this->input->post("rentalUid")->asString();
        $rental = new Rental();
        if ($rental->loadByRentalUid($rentalUid)->status == false) {
            $this->failed("Unable to find rental");
            return;
        }
        $package = $rental->relatedPackage()->getFirst();
        if ($package == null) {
            $this->failed("Unable to load package");
            return;
        }
        if ($forceCheckAv != null) {
            if ($forceCheckAv->getId() != $rental->getAvatarLink()) {
                $this->failed("Unable to renew someone elses rental via your hud, 
                please use the proxy pay!");
                return;
            }
        }
        $masterAvatar = new Avatar();
        if ($masterAvatar->loadID($this->siteConfig->getSlConfig()->getOwnerAvatarLink())->status == false) {
            $this->failed("Unable to load system owner");
            return;
        }
        $this->setSwapTag("systemowner", $masterAvatar->getAvatarUUID());
        $this->setSwapTag("cost", $package->getCost());
        $this->setSwapTag("word", "Day");
        if ($package->getDays() == 7) {
            $this->setSwapTag("word", "Week");
        } elseif ($package->getDays() == 31) {
            $this->setSwapTag("word", "Month");
        }
        $this->setSwapTag("days", $package->getDays());
        $this->ok($this->timeRemainingHumanReadable($rental->getExpireUnixtime()));
    }
    public function process(): void
    {
        $this->getCostOfRental(null);
    }
}
