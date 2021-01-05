<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventenablestart extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "buy";
        $this->functionname = "event_enable_start";
    }
}
