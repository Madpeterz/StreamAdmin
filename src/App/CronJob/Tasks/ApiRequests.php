<?php

namespace App\CronJob\Tasks;

use App\CronJob\Master\Master;

class ApiRequests extends Master
{
    protected string $cronName = "apirequests";
    protected int $cronID = 3;
    protected ?int $lockMaxGroups = 3;
    protected string $cronRunClass = "App\\Endpoint\\SecondLifeApi\\Apirequests\\Next";
}
