<?php
$server = new server();
$input = new inputFilter();
$domain = $input->postFilter("domain");
$controlpanel_url = $input->postFilter("controlpanel_url");
$failed_on = "";
$redirect = "";
if(strlen($domain) > 100) $failed_on .= $lang["server.cr.error.1"];
else if(strlen($domain) < 5) $failed_on .= $lang["server.cr.error.2"];
else if(strlen($controlpanel_url) < 5) $failed_on .= $lang["server.cr.error.3"];
else if($server->load_by_field("domain",$domain) == true) $failed_on .= $lang["server.cr.error.4"];
$status = false;
if($failed_on == "")
{
    $server = new server();
    $server->set_field("domain",$domain);
    $server->set_field("controlpanel_url",$controlpanel_url);
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
