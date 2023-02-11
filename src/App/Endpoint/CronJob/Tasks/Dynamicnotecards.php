<?php

namespace App\Endpoint\CronJob\Tasks;

use App\Endpoint\CronJob\Master\Master;

class Dynamicnotecards extends Master
{
    protected string $cronName = "notecardsserver";
    protected int $cronID = 3;
    protected string $cronRunClass = "App\\Endpoint\\Secondlifeapi\\Bot\\Notecardsync";
}
