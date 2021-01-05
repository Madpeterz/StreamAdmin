<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventcleardjs extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "revoke";
        $this->functionname = "event_clear_djs";
    }
}
