<?php

namespace App\CronJob\Tasks;

use App\CronJob\Master\Master;

class ClientAutoSuspend extends Master
{
    protected string $cronName = "clientautosuspendserver";
    protected int $cronID = 1;
    protected string $cronRunClass = "App\\Endpoint\\SecondLifeApi\\ClientAutoSuspend\\Next";
}
