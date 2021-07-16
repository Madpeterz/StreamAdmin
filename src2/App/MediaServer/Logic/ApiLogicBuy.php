<?php

namespace App\MediaServer\Logic;

class ApiLogicBuy extends ApiLogicProcess
{
    protected array $steps = [
        "" => "eventStartSyncUsername",
        "eventStartSyncUsername" => "eventEnableStart",
        "eventEnableStart" => "core_send_details",
    ];
}
