<?php

namespace App\CronJob\Tasks;

use App\Endpoint\View\Export\Flow1;

class ExportFlow1
{
    public function __construct()
    {
        $worker = new Flow1();
        $worker->makeSheet();
        $worker->renderPage();
    }
}
