<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

class OptAutodjNext extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "opt";
        $this->functionname = "optAutodjNext";
    }
}
