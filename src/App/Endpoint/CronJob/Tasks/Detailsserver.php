<?php

namespace App\Endpoint\CronJob\Tasks;

use App\Endpoint\CronJob\Master\Master;

class Detailsserver extends Master
{
    protected string $cronName = "detailsserver";
    protected int $cronID = 1;
    protected string $cronRunClass = "App\\Endpoint\\Secondlifeapi\\Detailsserver\\Next";
}
