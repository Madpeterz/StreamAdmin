<?php

namespace App\Endpoints\SecondLifeHudApi\Rentals;

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
        $rental_uid = $input->postFilter("uid");
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
        $server = new Server();
        if ($server->loadID($stream->getServerlink()) == false) {
            $this->setSwapTag("message", "Unable to find server");
            return;
        }
        $serverapi = new Apis();
        if ($serverapi->loadID($server->getApilink()) == false) {
            $this->setSwapTag("message", "Unable to load API");
            return;
        }
        $flags = [
            "autodjnext" => "opt_autodj_next",
            "toggleautodj" => "opt_toggle_autodj",
            "togglestate" => "opt_toggle_status",
            "resetpw" => "opt_password_reset",
        ];
        $this->setSwapTag("status", "true");
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
