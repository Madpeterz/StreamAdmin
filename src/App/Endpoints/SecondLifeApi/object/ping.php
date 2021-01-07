<?php

namespace App\Endpoints\SecondLifeApi\Object;

use App\Template\SecondlifeAjax;

class Ping extends SecondlifeAjax
{
    public function process(): void
    {
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "pong");
    }
}
