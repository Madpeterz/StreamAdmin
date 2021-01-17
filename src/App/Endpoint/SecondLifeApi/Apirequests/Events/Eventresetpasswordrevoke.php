<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventresetpasswordrevoke extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "revoke";
        $this->functionname = "event_reset_password_revoke";
    }
}
