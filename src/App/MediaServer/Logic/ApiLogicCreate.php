<?php

namespace App\MediaServer\Logic;

class ApiLogicCreate extends ApiLogicProcess
{
    protected array $steps = [
        "" => "eventCreateStream",
    ];
}
