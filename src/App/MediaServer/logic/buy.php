<?php

$steps = [
    "" => "eventStartSyncUsername",
    "eventStartSyncUsername" => "eventEnableStart",
    "eventEnableStart" => "core_send_details",
];
include "shared/media_server_apis/logic/process.php";
