<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Eventenablerenew extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "renew";
        $this->functionname = "event_enable_renew";
    }
}
