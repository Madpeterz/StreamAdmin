<?php
$steps = array(
    "" => "event_reset_password_revoke",
    "event_reset_password_revoke" => "event_clear_djs",
    "event_clear_djs" => "event_start_sync_username",
    "event_start_sync_username" => "event_disable_revoke",
);
include("site/api_serverlogic/process.php");
?>
