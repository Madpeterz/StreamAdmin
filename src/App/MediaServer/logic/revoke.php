<?php

$steps = [
    "" => "eventRecreateRevoke",
    "recreate_not_enabled" => "eventResetPasswordRevoke",
    "eventResetPasswordRevoke" => "eventClearDjs",
    "eventClearDjs" => "eventRevokeResetUsername",
    "eventRevokeResetUsername" => "eventDisableRevoke",
];
include "shared/media_server_apis/logic/process.php";
