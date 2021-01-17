<?php

namespace App\Endpoints\SecondLifeApi\Apirequests\Events;

class Opttoggleautodj extends CallApi
{
    protected function configEvent(): void
    {
        $this->logic_step = "opt";
        $this->functionname = "opt_toggle_autodj";
    }
}
