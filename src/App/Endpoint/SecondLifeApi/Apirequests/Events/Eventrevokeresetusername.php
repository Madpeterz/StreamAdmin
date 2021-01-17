<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventrevokeresetusername extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "revoke";
        $this->functionname = "event_revoke_reset_username";
    }
}
