<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class EventResetPasswordRevoke extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "revoke";
        $this->functionname = "eventResetPasswordRevoke";
    }
}
