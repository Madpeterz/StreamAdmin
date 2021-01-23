<?php

namespace App\MediaServer\Logic;

class ApiLogicExpire extends ApiLogicProcess
{
    protected array $steps = [
        "" => "eventDisableExpire",
    ];
}
