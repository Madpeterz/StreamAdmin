<?php
$steps = array(
    "" => "event_recreate_revoke",
    "recreate_not_enabled" => "event_reset_password_revoke",
    "event_reset_password_revoke" => "event_clear_djs",
    "event_clear_djs" => "event_revoke_reset_username",
    "event_revoke_reset_username" => "event_disable_revoke",
);
include "shared/media_server_apis/logic/process.php";
?>
