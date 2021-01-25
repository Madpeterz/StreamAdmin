<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

use App\Models\Apis;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Getapistatus extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rentalUid = $input->postFilter("uid");
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
        $server = new Server();
        if ($server->loadID($stream->getServerLink()) == false) {
            $this->setSwapTag("message", "Unable to find server");
            return;
        }
        $serverapi = new Apis();
        if ($serverapi->loadID($server->getApiLink()) == false) {
            $this->setSwapTag("message", "Unable to load API");
            return;
        }
        $flags = [
            "autodjnext" => "optAutodjNext",
            "toggleautodj" => "optToggleAutodj",
            "togglestate" => "optToggleStatus",
            "resetpw" => "optPasswordReset",
        ];
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "seeflags");
        foreach ($flags as $key => $dataset) {
            $state = 0;
            $code = "get" . ucfirst($dataset);
            if ($server->$code() == true) {
                if ($serverapi->$code() == true) {
                    $state = 1;
                }
            }
            $this->setSwapTag($key, $state);
        }
    }
}
