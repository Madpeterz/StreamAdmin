<?php

namespace App\Endpoint\SystemApi\Event;

use App\Template\ViewAjax;
use server_secondbot;

class Next extends ViewAjax
{
    public function process(): void
    {
        $server_secondbot = new server_secondbot();
        $server_secondbot->next_event();
    }
}
