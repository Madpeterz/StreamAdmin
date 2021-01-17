<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Optautodjnext extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "opt";
        $this->functionname = "opt_autodj_next";
    }
}
