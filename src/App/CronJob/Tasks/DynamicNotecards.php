<?php

namespace App\CronJob\Tasks;

use App\CronJob\Master\Master;

class DynamicNotecards extends Master
{
    protected string $cronName = "notecardsserver";
    protected int $cronID = 5;
    protected string $cronRunClass = "App\\Endpoint\\SecondLifeApi\\Bot\\Notecardsync";
}
