<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class OptToggleAutodj extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "opt";
        $this->functionname = "optToggleAutodj";
    }
}
