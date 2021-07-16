<?php

namespace App\MediaServer\Logic;

class ApiLogicRevoke extends ApiLogicProcess
{
    protected array $steps = [
        "" => "eventRecreateRevoke",
        "recreate_not_enabled" => "eventResetPasswordRevoke",
        "eventResetPasswordRevoke" => "eventClearDjs",
        "eventClearDjs" => "eventRevokeResetUsername",
        "eventRevokeResetUsername" => "eventDisableRevoke",
    ];
}
