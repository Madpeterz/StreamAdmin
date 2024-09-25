<?php

namespace App\Endpoint\Secondlifeapi\Object;

use App\Template\SecondlifeAjax;

class Ping extends SecondlifeAjax
{
    public function process(): void
    {
        $this->ok("pong");
    }
}
