<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventdisableexpire extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "expire";
        $this->functionname = "event_disable_expire";
    }
}
