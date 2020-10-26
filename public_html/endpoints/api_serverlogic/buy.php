<?php
$steps = array(
    "" => "event_start_sync_username",
    "event_start_sync_username" => "event_enable_start",
    "event_enable_start" => "core_send_details"
);
include("shared/api_serverlogic/process.php");
?>
