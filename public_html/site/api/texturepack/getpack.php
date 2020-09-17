<?php
$input = new inputFilter();
$texturepack = $input->postFilter("texturepack","integer");
if($texturepack > 0)
{
    $textureconfig = new textureconfig();
    if($textureconfig->load($texturepack) == true)
    {
        $status = true;
        $reply["texture_offline"] = $textureconfig->get_offline();
        $reply["texture_waitingforowner"] = $textureconfig->get_wait_owner();
        $reply["texture_fetchingdetails"] = $textureconfig->get_getting_details();
        $reply["texture_request_payment"] = $textureconfig->get_make_payment();
        $reply["texture_renewhere"] = $textureconfig->get_renew_here();
        $reply["texture_inuse"] = $textureconfig->get_inuse();
        $reply["texture_request_details"] = $textureconfig->get_request_details();
        $reply["texture_stock_levels"] = $textureconfig->get_stock_levels();
        $reply["texture_proxyrenew"] = $textureconfig->get_proxyrenew();
        $reply["texture_treevend_waiting"] = $textureconfig->get_treevend_waiting();

        // reseller config (send anyway even if not wanted)
        if($owner_override == false)
        {
            $reply["reseller_rate"] = $reseller->get_rate();
            $reply["reseller_mode"] = $lang["texturepack.gp.info.1"];
        }
        else
        {
            $reply["reseller_rate"] = 100;
            $reply["reseller_mode"] = $lang["texturepack.gp.info.2"];
        }
        print "ok";
    }
    else
    {
        print $lang["texturepack.gp.error.2"];
    }
}
else
{
    print $lang["texturepack.gp.error.1"];
}
?>
