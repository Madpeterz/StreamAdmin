<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Optpasswordreset extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "opt";
        $this->functionname = "opt_password_reset";
    }
}
