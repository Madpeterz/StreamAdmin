<?php
$package = new package();
$server = new server();
$input = new inputFilter();

$name = $input->postFilter("name");
$getting_details = $input->postFilter("getting_details");
$request_details = $input->postFilter("request_details");
$offline = $input->postFilter("offline");
$wait_owner = $input->postFilter("wait_owner");
$inuse = $input->postFilter("inuse");
$make_payment = $input->postFilter("make_payment");
$stock_levels = $input->postFilter("stock_levels");
$renew_here = $input->postFilter("renew_here");
$proxyrenew = $input->postFilter("proxyrenew");
$treevend_waiting = $input->postFilter("treevend_waiting");
$failed_on = "";
if(strlen($name) < 4) $failed_on .= $lang["textureconfig.cr.error.1"];
else if(strlen($name) > 30) $failed_on .= $lang["textureconfig.cr.error.2"];
else if(strlen($getting_details) != 36) $failed_on .= $lang["textureconfig.cr.error.3"];
else if(strlen($request_details) != 36) $failed_on .= $lang["textureconfig.cr.error.4"];
else if(strlen($offline) != 36) $failed_on .= $lang["textureconfig.cr.error.5"];
else if(strlen($wait_owner) != 36) $failed_on .= $lang["textureconfig.cr.error.6"];
else if(strlen($inuse) != 36) $failed_on .= $lang["textureconfig.cr.error.7"];
else if(strlen($make_payment) != 36) $failed_on .= $lang["textureconfig.cr.error.8"];
else if(strlen($stock_levels) != 36) $failed_on .= $lang["textureconfig.cr.error.9"];
else if(strlen($renew_here) != 36) $failed_on .= $lang["textureconfig.cr.error.10"];
else if(strlen($proxyrenew) != 36) $failed_on .= $lang["textureconfig.cr.error.11"];
else if(strlen($treevend_waiting) != 36) $failed_on .= $lang["textureconfig.cr.error.12"];
$status = false;
if($failed_on == "")
{
    $textureconfig = new textureconfig();
    $textureconfig->set_name($name);
    $textureconfig->set_offline($offline);
    $textureconfig->set_wait_owner($wait_owner);
    $textureconfig->set_stock_levels($stock_levels);
    $textureconfig->set_make_payment($make_payment);
    $textureconfig->set_inuse($inuse);
    $textureconfig->set_renew_here($renew_here);
    $textureconfig->set_getting_details($getting_details);
    $textureconfig->set_request_details($request_details);
    $textureconfig->set_proxyrenew($proxyrenew);
    $textureconfig->set_treevend_waiting($treevend_waiting);
    $create_status = $textureconfig->create_entry();
    if($create_status["status"] == true)
    {
        $status = true;
        $redirect = "textureconfig";
        echo $lang["textureconfig.cr.info.1"];
    }
    else
    {
        echo sprintf($lang["textureconfig.cr.error.13"],$create_status["message"]);
    }
}
else
{
    echo $failed_on;
}
?>
