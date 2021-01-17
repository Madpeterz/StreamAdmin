<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventrecreaterevoke extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "revoke";
        $this->functionname = "event_recreate_revoke";
    }
}
