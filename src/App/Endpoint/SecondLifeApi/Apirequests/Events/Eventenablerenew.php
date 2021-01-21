<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class Eventenablerenew extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "renew";
        $this->functionname = "eventEnableRenew";
    }
}
