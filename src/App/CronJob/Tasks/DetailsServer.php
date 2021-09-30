<?php

namespace App\CronJob\Tasks;

use App\CronJob\Master\Master;

class DetailsServer extends Master
{
    protected string $cronName = "detailsserver";
    protected int $cronID = 2;
    protected string $cronRunClass = "App\\Endpoint\\SecondLifeApi\\Detailsserver\\Next";
}
