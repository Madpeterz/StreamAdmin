<?php
$avatar = new avatar();
$input = new inputFilter();
$sllinkcode = $input->postFilter("sllinkcode");
$httpcode = $input->postFilter("httpcode");
$publiclinkcode = $input->postFilter("publiclinkcode");
$new_resellers_rate = $input->postFilter("new_resellers_rate","integer");
$new_resellers = $input->postFilter("new_resellers","bool");
$event_storage = $input->postFilter("event_storage","bool");
$owneravuid = $input->postFilter("owneravuid");
$ui_tweaks_clients_fulllist = $input->postFilter("ui_tweaks_clients_fulllist","bool");
$ui_tweaks_datatable_itemsperpage = $input->postFilter("ui_tweaks_datatable_itemsperpage","integer");


$failed_on = "";
if(strlen($sllinkcode) < 5) $failed_on .= $lang["slconfig.up.error.1"];
else if(strlen($sllinkcode) > 30) $failed_on .= $lang["slconfig.up.error.2"];
if(strlen($httpcode) < 5) $failed_on .= $lang["slconfig.up.error.3"];
else if(strlen($httpcode) > 30) $failed_on .= $lang["slconfig.up.error.4"];
else if($new_resellers_rate < 0) $failed_on .= $lang["slconfig.up.error.5"];
else if($new_resellers_rate > 100) $failed_on .= $lang["slconfig.up.error.6"];
else if($ui_tweaks_datatable_itemsperpage < 10) $failed_on .= $lang["slconfig.up.error.10"];
else if($ui_tweaks_datatable_itemsperpage > 200) $failed_on .= $lang["slconfig.up.error.11"];
else if(strlen($owneravuid) != 8) $failed_on .= $lang["slconfig.up.error.7"];
else if($avatar->load_by_field("avatar_uid",$owneravuid) == false) $failed_on .= $lang["slconfig.up.error.8"];
$redirect = "slconfig";
$status = false;
if($failed_on == "")
{
    if($avatar->get_id() != $slconfig->get_owner_av())
    {
        $slconfig->set_owner_av($avatar->get_id());
    }
    $slconfig->set_sllinkcode($sllinkcode);
    $slconfig->set_publiclinkcode($publiclinkcode);
    $slconfig->set_http_inbound_secret($httpcode);
    $slconfig->set_new_resellers($new_resellers);
    $slconfig->set_new_resellers_rate($new_resellers_rate);
    $slconfig->set_eventstorage($event_storage);
    $slconfig->set_clients_list_mode($ui_tweaks_clients_fulllist);
    $slconfig->set_datatable_itemsperpage($ui_tweaks_datatable_itemsperpage);
    if($session->get_ownerlevel() == 1)
    {
        $smtp_from = $input->postFilter("smtp_from");
        $smtp_reply = $input->postFilter("smtp_reply");
        $smtp_host = $input->postFilter("smtp_host");
        $smtp_user = $input->postFilter("smtp_user");
        $smtp_code = $input->postFilter("smtp_code");
        $smtp_port = $input->postFilter("smtp_port");
        // missing tests here :P
        $slconfig->set_smtp_host($smtp_host);
        $slconfig->set_smtp_port($smtp_port);
        if($smtp_user != "skip") $slconfig->set_smtp_username($smtp_user);
        if($smtp_code != "skip") $slconfig->set_smtp_accesscode($smtp_code);
        $slconfig->set_smtp_from($smtp_from);
        $slconfig->set_smtp_replyto($smtp_reply);
    }
    $update_status = $slconfig->save_changes();
    if($update_status["status"] == true)
    {
        $status = true;
        echo $lang["slconfig.up.info.1"];
    }
    else
    {
        echo sprintf($lang["slconfig.up.error.9"],$update_status["message"]);
    }
}
else
{
    $status = false;
    $redirect = "";
    echo $failed_on;
}
?>
