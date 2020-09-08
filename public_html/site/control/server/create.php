<?php
$apis = new apis_set();
$apis->loadAll();
$server = new server();
$input = new inputFilter();
$domain = $input->postFilter("domain");
$controlpanel_url = $input->postFilter("controlpanel_url");
$apilink = $input->postFilter("apilink","integer");
$api_username = $input->postFilter("api_username");
$api_password = $input->postFilter("api_password");
$opt_password_reset = $input->postFilter("opt_password_reset","integer");
$opt_autodj_next = $input->postFilter("opt_autodj_next","integer");
$opt_toggle_autodj = $input->postFilter("opt_toggle_autodj","integer");
$event_enable_start = $input->postFilter("event_enable_start","integer");
$event_disable_expire = $input->postFilter("event_disable_expire","integer");
$event_disable_revoke = $input->postFilter("event_disable_revoke","integer");
$event_reset_password_revoke = $input->postFilter("event_reset_password_revoke","integer");
$event_enable_renew = $input->postFilter("event_enable_renew","integer");

$failed_on = "";
$redirect = "";
$yesno_array = array(0,1);
if(strlen($domain) > 100) $failed_on .= $lang["server.cr.error.1"];
else if(strlen($domain) < 5) $failed_on .= $lang["server.cr.error.2"];
else if(strlen($controlpanel_url) < 5) $failed_on .= $lang["server.cr.error.3"];
else if($server->load_by_field("domain",$domain) == true) $failed_on .= $lang["server.cr.error.4"];
else if(in_array($apilink,$apis->get_all_ids()) == false) $failed_on .= $lang["server.cr.error.6"];
else if(in_array($opt_password_reset,$yesno_array) == false) $failed_on .= $lang["server.cr.error.7"];
else if(in_array($opt_autodj_next,$yesno_array) == false) $failed_on .= $lang["server.cr.error.8"];
else if(in_array($opt_toggle_autodj,$yesno_array) == false) $failed_on .= $lang["server.cr.error.9"];
else if(in_array($event_enable_start,$yesno_array) == false) $failed_on .= $lang["server.cr.error.10"];
else if(in_array($event_disable_expire,$yesno_array) == false) $failed_on .= $lang["server.cr.error.11"];
else if(in_array($event_disable_revoke,$yesno_array) == false) $failed_on .= $lang["server.cr.error.12"];
else if(in_array($event_reset_password_revoke,$yesno_array) == false) $failed_on .= $lang["server.cr.error.13"];
else if(in_array($event_enable_renew,$yesno_array) == false) $failed_on .= $lang["server.cr.error.14"];

$status = false;
if($failed_on == "")
{
    $server = new server();
    $server->set_field("domain",$domain);
    $server->set_field("controlpanel_url",$controlpanel_url);
    $server->set_field("apilink",$apilink);
    $server->set_field("api_username",$api_username);
    $server->set_field("api_password",$api_password);
    $server->set_field("opt_password_reset",$opt_password_reset);
    $server->set_field("opt_autodj_next",$opt_autodj_next);
    $server->set_field("opt_toggle_autodj",$opt_toggle_autodj);
    $server->set_field("event_enable_start",$event_enable_start);
    $server->set_field("event_disable_expire",$event_disable_expire);
    $server->set_field("event_disable_revoke",$event_disable_revoke);
    $server->set_field("event_reset_password_revoke",$event_reset_password_revoke);
    $server->set_field("event_enable_renew",$event_enable_renew);
    $create_status = $server->create_entry();
    if($create_status["status"] == true)
    {
        $status = true;
        $redirect = "server";
        echo $lang["server.cr.info.1"];
    }
    else
    {
        echo sprintf($lang["server.cr.error.5"],$create_status["message"]);
    }
}
else
{
    echo $failed_on;
}
?>
