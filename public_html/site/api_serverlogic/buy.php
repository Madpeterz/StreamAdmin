<?php
$steps = array(
    "" => "event_enable_start",
    "event_enable_start" => "event_start_sync_username",
    "event_start_sync_username" => "core_send_details"
);
include("site/api_serverlogic/process.php");
?>
