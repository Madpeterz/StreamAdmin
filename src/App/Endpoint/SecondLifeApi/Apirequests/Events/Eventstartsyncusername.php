<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventstartsyncusername extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "buy";
        $this->functionname = "event_start_sync_username";
    }
}
