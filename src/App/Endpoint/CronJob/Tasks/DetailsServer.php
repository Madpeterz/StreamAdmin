<?php

namespace App\Endpoint\CronJob\Tasks;

use App\Endpoint\CronJob\Master\Master;

class DetailsServer extends Master
{
    protected string $cronName = "detailsserver";
    protected int $cronID = 2;
    protected string $cronRunClass = "App\\Endpoint\\Secondlifeapi\\Detailsserver\\Next";
}
