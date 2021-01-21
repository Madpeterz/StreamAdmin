<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class Eventresetpasswordrevoke extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "revoke";
        $this->functionname = "eventResetPasswordRevoke";
    }
}
