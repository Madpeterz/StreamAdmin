<?php

$ajax_reply->purge_cache_file("current_timezone", false);

$avatar = new avatar();
$timezone = new timezones();
$input = new inputFilter();
$sllinkcode = $input->postFilter("sllinkcode");
$httpcode = $input->postFilter("httpcode");
$publiclinkcode = $input->postFilter("publiclinkcode");
$new_resellers_rate = $input->postFilter("new_resellers_rate", "integer");
$new_resellers = $input->postFilter("new_resellers", "bool");
$event_storage = $input->postFilter("event_storage", "bool");
$owneravuid = $input->postFilter("owneravuid");
$ui_tweaks_clients_fulllist = $input->postFilter("ui_tweaks_clients_fulllist", "bool");
$ui_tweaks_datatable_itemsperpage = $input->postFilter("ui_tweaks_datatable_itemsperpage", "integer");
$api_default_email = $input->postFilter("api_default_email", "email");
$displaytimezonelink = $input->postFilter("displaytimezonelink", "integer");



$failed_on = "";
if (strlen($sllinkcode) < 5) {
    $failed_on .= $lang["slconfig.up.error.1"];
} elseif (strlen($sllinkcode) > 10) {
    $failed_on .= $lang["slconfig.up.error.2"];
}
if (strlen($httpcode) < 5) {
    $failed_on .= $lang["slconfig.up.error.3"];
} elseif (strlen($httpcode) > 30) {
    $failed_on .= $lang["slconfig.up.error.4"];
} elseif ($new_resellers_rate < 0) {
    $failed_on .= $lang["slconfig.up.error.5"];
} elseif ($new_resellers_rate > 100) {
    $failed_on .= $lang["slconfig.up.error.6"];
} elseif ($ui_tweaks_datatable_itemsperpage < 10) {
    $failed_on .= $lang["slconfig.up.error.10"];
} elseif ($ui_tweaks_datatable_itemsperpage > 200) {
    $failed_on .= $lang["slconfig.up.error.11"];
} elseif (strlen($owneravuid) != 8) {
    $failed_on .= $lang["slconfig.up.error.7"];
} elseif ($avatar->loadByField("avatar_uid", $owneravuid) == false) {
    $failed_on .= $lang["slconfig.up.error.8"];
} elseif ($timezone->load($displaytimezonelink) == false) {
    $failed_on .= $lang["slconfig.up.error.12"];
} elseif (strlen($api_default_email) < 7) {
    $failed_on .= $lang["slconfig.up.error.13"];
} elseif (strlen($publiclinkcode) < 6) {
    $failed_on .= $lang["slconfig.up.error.14"];
} elseif (strlen($publiclinkcode) > 12) {
    $failed_on .= $lang["slconfig.up.error.15"];
}


$ajax_reply->set_swap_tag_string("redirect", "slconfig");
$status = false;
if ($failed_on == "") {
    if ($avatar->getId() != $slconfig->get_owner_av()) {
        $slconfig->set_owner_av($avatar->getId());
    }
    $slconfig->set_sllinkcode($sllinkcode);
    $slconfig->set_publiclinkcode($publiclinkcode);
    $slconfig->set_http_inbound_secret($httpcode);
    $slconfig->set_new_resellers($new_resellers);
    $slconfig->set_new_resellers_rate($new_resellers_rate);
    $slconfig->set_eventstorage($event_storage);
    $slconfig->set_clients_list_mode($ui_tweaks_clients_fulllist);
    $slconfig->set_datatable_itemsperpage($ui_tweaks_datatable_itemsperpage);
    $slconfig->set_displaytimezonelink($displaytimezonelink);
    $slconfig->set_api_default_email($api_default_email);
    if ($session->get_ownerlevel() == 1) {
        $smtp_from = $input->postFilter("smtp_from");
        $smtp_reply = $input->postFilter("smtp_reply");
        $smtp_host = $input->postFilter("smtp_host");
        $smtp_user = $input->postFilter("smtp_user");
        $smtp_code = $input->postFilter("smtp_code");
        $smtp_port = $input->postFilter("smtp_port");
        // missing tests here :P
        $slconfig->set_smtp_host($smtp_host);
        $slconfig->set_smtp_port($smtp_port);
        if ($smtp_user != "skip") {
            $slconfig->set_smtp_username($smtp_user);
        }
        if ($smtp_code != "skip") {
            $slconfig->set_smtp_accesscode($smtp_code);
        }
        $slconfig->set_smtp_from($smtp_from);
        $slconfig->set_smtp_replyto($smtp_reply);
    }
    $update_status = $slconfig->save_changes();
    if ($update_status["status"] == true) {
        $status = true;
        $ajax_reply->set_swap_tag_string("message", $lang["slconfig.up.info.1"]);
    } else {
        $ajax_reply->set_swap_tag_string("message", sprintf($lang["slconfig.up.error.9"], $update_status["message"]));
    }
} else {
    $status = false;
    $ajax_reply->set_swap_tag_string("message", $failed_on);
    $ajax_reply->set_swap_tag_string("redirect", "");
}
