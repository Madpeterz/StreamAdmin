<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class EventDisableExpire extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "expire";
        $this->functionname = "eventDisableExpire";
    }
}
