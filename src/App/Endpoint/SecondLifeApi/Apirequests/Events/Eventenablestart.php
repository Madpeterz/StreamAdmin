<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class Eventenablestart extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "buy";
        $this->functionname = "eventEnableStart";
    }
}
