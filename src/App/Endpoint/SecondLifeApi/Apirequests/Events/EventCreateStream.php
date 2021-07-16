<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class EventCreateStream extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "create";
        $this->functionname = "eventCreateStream";
    }
}
