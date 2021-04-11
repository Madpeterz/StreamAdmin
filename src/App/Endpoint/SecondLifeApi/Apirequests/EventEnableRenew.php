<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class EventEnableRenew extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "renew";
        $this->functionname = "eventEnableRenew";
    }
}
