<?php

namespace App\Endpoints\SecondLifeApi\Renew;

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
        $rental_uid = $input->postFilter("rental_uid");
        $rental = new Rental();
        if ($rental->loadByField("rental_uid", $rental_uid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamlink()) == false) {
            $this->setSwapTag("message", "Unable to find stream");
            return;
        }
        $package = new Package();
        if ($package->loadID($stream->getPackagelink()) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("cost", $package->getCost());
        $this->setSwapTag("message", timeleft_hours_and_days($rental->getExpireunixtime()));
    }
}
