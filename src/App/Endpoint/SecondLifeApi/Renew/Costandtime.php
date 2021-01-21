<?php

namespace App\Endpoint\SecondLifeApi\Renew;

use App\Models\Package;
use App\Models\Rental;
use App\Models\Stream;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Costandtime extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rentalUid = $input->postFilter("rentalUid");
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $rentalUid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to find stream");
            return;
        }
        $package = new Package();
        if ($package->loadID($stream->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("cost", $package->getCost());
        $this->setSwapTag("message", timeleft_hours_and_days($rental->getExpireUnixtime()));
    }
}
