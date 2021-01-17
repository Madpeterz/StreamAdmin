<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventdisablerevoke extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "revoke";
        $this->functionname = "event_disable_revoke";
    }
}
