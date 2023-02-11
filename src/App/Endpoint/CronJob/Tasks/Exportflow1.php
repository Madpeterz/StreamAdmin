<?php

namespace App\Endpoint\CronJob\Tasks;

use App\Endpoint\View\Export\Flow1;

class Exportflow1
{
    protected int $cronID = 4;
    public function __construct()
    {
        $worker = new Flow1();
        $worker->makeSheet();
        $worker->renderPage();
    }
}
