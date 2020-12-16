<?php

$apis = new apis_set();
$apis->loadAll();
$server = new server();
$input = new inputFilter();
$domain = $input->postFilter("domain");
$controlpanel_url = $input->postFilter("controlpanel_url");
$apilink = $input->postFilter("apilink", "integer");
$api_url = $input->postFilter("api_url");
$api_username = $input->postFilter("api_username");
$api_password = $input->postFilter("api_password");
$opt_password_reset = $input->postFilter("opt_password_reset", "integer");
$opt_autodj_next = $input->postFilter("opt_autodj_next", "integer");
$opt_toggle_autodj = $input->postFilter("opt_toggle_autodj", "integer");
$event_enable_start = $input->postFilter("event_enable_start", "integer");
$event_disable_expire = $input->postFilter("event_disable_expire", "integer");
$event_disable_revoke = $input->postFilter("event_disable_revoke", "integer");
$event_reset_password_revoke = $input->postFilter("event_reset_password_revoke", "integer");
$event_enable_renew = $input->postFilter("event_enable_renew", "integer");
$opt_toggle_status = $input->postFilter("opt_toggle_status", "integer");
$event_start_sync_username = $input->postFilter("event_start_sync_username", "integer");
$api_serverstatus = $input->postFilter("api_serverstatus", "integer");
$event_clear_djs = $input->postFilter("event_clear_djs", "integer");
$event_revoke_reset_username = $input->postFilter("event_revoke_reset_username", "integer");
$event_recreate_revoke = $input->postFilter("event_recreate_revoke", "integer");
$api_sync_accounts = $input->postFilter("api_sync_accounts", "integer");
$event_create_stream = $input->postFilter("event_create_stream", "integer");
$event_update_stream = $input->postFilter("event_update_stream", "integer");

$failed_on = "";
$this->output->setSwapTagString("redirect", "");
$yesno_array = [0,1];
if (strlen($domain) > 100) {
    $failed_on .= $lang["server.cr.error.1"];
} elseif (strlen($domain) < 5) {
    $failed_on .= $lang["server.cr.error.2"];
} elseif (strlen($controlpanel_url) < 5) {
    $failed_on .= $lang["server.cr.error.3"];
} elseif ($server->loadByField("domain", $domain) == true) {
    $failed_on .= $lang["server.cr.error.4"];
} elseif (in_array($apilink, $apis->getAllIds()) == false) {
    $failed_on .= $lang["server.cr.error.6"];
} elseif (in_array($opt_password_reset, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.7"];
} elseif (in_array($opt_autodj_next, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.8"];
} elseif (in_array($opt_toggle_autodj, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.9"];
} elseif (in_array($event_enable_start, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.10"];
} elseif (in_array($event_disable_expire, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.11"];
} elseif (in_array($event_disable_revoke, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.12"];
} elseif (in_array($event_reset_password_revoke, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.13"];
} elseif (in_array($event_enable_renew, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.14"];
} elseif (in_array($opt_toggle_status, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.15"];
} elseif (in_array($event_start_sync_username, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.16"];
} elseif (in_array($api_serverstatus, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.17"];
} elseif (in_array($event_clear_djs, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.18"];
} elseif (in_array($event_revoke_reset_username, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.19"];
} elseif (in_array($event_recreate_revoke, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.20"];
} elseif (in_array($api_sync_accounts, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.21"];
} elseif (in_array($event_create_stream, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.22"];
} elseif (in_array($event_update_stream, $yesno_array) == false) {
    $failed_on .= $lang["server.cr.error.23"];
}

$status = false;
if ($failed_on == "") {
    $server = new server();
    $server->set_domain($domain);
    $server->set_controlpanel_url($controlpanel_url);
    $server->set_apilink($apilink);
    $server->set_api_url($api_url);
    $server->set_api_username($api_username);
    $server->set_api_password($api_password);
    $server->set_opt_password_reset($opt_password_reset);
    $server->set_opt_autodj_next($opt_autodj_next);
    $server->set_opt_toggle_autodj($opt_toggle_autodj);
    $server->set_event_enable_start($event_enable_start);
    $server->set_event_disable_expire($event_disable_expire);
    $server->set_event_disable_revoke($event_disable_revoke);
    $server->set_event_reset_password_revoke($event_reset_password_revoke);
    $server->set_event_enable_renew($event_enable_renew);
    $server->set_opt_toggle_status($opt_toggle_status);
    $server->set_event_start_sync_username($event_start_sync_username);
    $server->set_api_serverstatus($api_serverstatus);
    $server->set_event_clear_djs($event_clear_djs);
    $server->set_event_revoke_reset_username($event_revoke_reset_username);
    $server->set_event_recreate_revoke($event_recreate_revoke);
    $server->set_api_sync_accounts($api_sync_accounts);
    $server->set_event_create_stream($event_create_stream);
    $server->set_event_update_stream($event_update_stream);
    $create_status = $server->createEntry();
    if ($create_status["status"] == true) {
        $status = true;
        $this->output->setSwapTagString("message", $lang["server.cr.info.1"]);
        $this->output->setSwapTagString("redirect", "server");
    } else {
        $this->output->setSwapTagString("message", sprintf($lang["server.cr.error.5"], $create_status["message"]));
    }
} else {
    $this->output->setSwapTagString("message", $failed_on);
}
