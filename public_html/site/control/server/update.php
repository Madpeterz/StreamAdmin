<?php
$apis = new apis_set();
$apis->loadAll();
$input = new inputFilter();
$domain = $input->postFilter("domain");
$controlpanel_url = $input->postFilter("controlpanel_url");
$failed_on = "";
$apilink = $input->postFilter("apilink","integer");
$api_url = $input->postFilter("api_url");
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
$opt_toggle_status = $input->postFilter("opt_toggle_status","integer");
$event_start_sync_username = $input->postFilter("event_start_sync_username","integer");
$api_serverstatus = $input->postFilter("api_serverstatus","integer");
$event_clear_djs = $input->postFilter("event_clear_djs","integer");
$event_revoke_reset_username = $input->postFilter("event_revoke_reset_username","integer");
$event_recreate_revoke = $input->postFilter("event_recreate_revoke","integer");
$api_sync_accounts = $input->postFilter("api_sync_accounts","integer");

$yesno_array = array(0,1);
if(strlen($domain) > 100) $failed_on .= $lang["server.up.error.1"];
else if(strlen($domain) < 5) $failed_on .= $lang["server.up.error.2"];
else if(strlen($controlpanel_url) < 5) $failed_on .= $lang["server.up.error.3"];
else if(in_array($apilink,$apis->get_all_ids()) == false) $failed_on .= $lang["server.up.error.8"];
else if(in_array($opt_password_reset,$yesno_array) == false) $failed_on .= $lang["server.up.error.9"];
else if(in_array($opt_autodj_next,$yesno_array) == false) $failed_on .= $lang["server.up.error.10"];
else if(in_array($opt_toggle_autodj,$yesno_array) == false) $failed_on .= $lang["server.up.error.11"];
else if(in_array($event_enable_start,$yesno_array) == false) $failed_on .= $lang["server.up.error.12"];
else if(in_array($event_disable_expire,$yesno_array) == false) $failed_on .= $lang["server.up.error.13"];
else if(in_array($event_disable_revoke,$yesno_array) == false) $failed_on .= $lang["server.up.error.14"];
else if(in_array($event_reset_password_revoke,$yesno_array) == false) $failed_on .= $lang["server.up.error.15"];
else if(in_array($event_enable_renew,$yesno_array) == false) $failed_on .= $lang["server.up.error.16"];
else if(in_array($opt_toggle_status,$yesno_array) == false) $failed_on .= $lang["server.up.error.17"];
else if(in_array($event_start_sync_username,$yesno_array) == false) $failed_on .= $lang["server.up.error.18"];
else if(in_array($api_serverstatus,$yesno_array) == false) $failed_on .= $lang["server.up.error.19"];
else if(in_array($event_clear_djs,$yesno_array) == false) $failed_on .= $lang["server.up.error.20"];
else if(in_array($event_revoke_reset_username,$yesno_array) == false) $failed_on .= $lang["server.up.error.21"];
else if(in_array($event_recreate_revoke,$yesno_array) == false) $failed_on .= $lang["server.up.error.22"];
else if(in_array($api_sync_accounts,$yesno_array) == false) $failed_on .= $lang["server.up.error.23"];

$status = false;
if($failed_on == "")
{
    $server = new server();
    if($server->load($page) == true)
    {
        $where_fields = array(array("domain"=>"="));
        $where_values = array(array($domain =>"s"));
        $count_check = $sql->basic_count($server->get_table(),$where_fields,$where_values);
        $expected_count = 0;
        if($server->get_domain() == $domain)
        {
            $expected_count = 1;
        }
        if($count_check["status"] == true)
        {
            if($count_check["count"] == $expected_count)
            {
                $server->set_domain($domain);
                $server->set_controlpanel_url($controlpanel_url);
                $server->set_apilink($apilink);
                $server->set_api_url($api_url);
                $server->set_api_username($api_username);
                if($api_password != "NoChange") $server->set_api_password($api_password);
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
                $update_status = $server->save_changes();
                if($update_status["status"] == true)
                {
                    $status = true;
                    $ajax_reply->set_swap_tag_string("message",$lang["server.up.info.1"]);
                    $ajax_reply->set_swap_tag_string("redirect","server");
                }
                else
                {
                    $ajax_reply->set_swap_tag_string("message",sprintf($lang["server.up.error.7"],$update_status["message"]));
                }
            }
            else
            {
                $ajax_reply->set_swap_tag_string("message",$lang["server.up.error.6"]);
            }
        }
        else
        {
            $ajax_reply->set_swap_tag_string("message",$lang["server.up.error.5"]);
        }
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message",$lang["server.up.error.4"]);
        $ajax_reply->set_swap_tag_string("redirect","server");
    }
}
else
{
    $ajax_reply->set_swap_tag_string("message",$failed_on);
    $ajax_reply->set_swap_tag_string("redirect","server/manage/".$page."");
}
?>
