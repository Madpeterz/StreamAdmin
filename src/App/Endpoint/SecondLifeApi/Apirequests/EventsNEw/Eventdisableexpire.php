<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class Eventdisableexpire extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "expire";
        $this->functionname = "eventDisableExpire";
    }
}
