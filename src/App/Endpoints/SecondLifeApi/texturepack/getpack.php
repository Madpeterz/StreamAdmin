<?php

$input = new inputFilter();
$texturepack = $input->postFilter("texturepack", "integer");
if ($texturepack > 0) {
    $textureconfig = new textureconfig();
    if ($textureconfig->loadID($texturepack) == true) {
        $status = true;
        $reply["texture_offline"] = $textureconfig->getOffline();
        $reply["texture_waitingforowner"] = $textureconfig->getWait_owner();
        $reply["texture_fetchingdetails"] = $textureconfig->getGetting_details();
        $reply["texture_request_payment"] = $textureconfig->getMake_payment();
        $reply["texture_renewhere"] = $textureconfig->getRenew_here();
        $reply["texture_inuse"] = $textureconfig->getInuse();
        $reply["texture_request_details"] = $textureconfig->getRequest_details();
        $reply["texture_stock_levels"] = $textureconfig->getStock_levels();
        $reply["texture_proxyrenew"] = $textureconfig->getProxyrenew();
        $reply["texture_treevend_waiting"] = $textureconfig->getTreevend_waiting();

        // reseller config (send anyway even if not wanted)
        if ($owner_override == false) {
            $reply["reseller_rate"] = $reseller->getRate();
            $reply["reseller_mode"] = $lang["texturepack.gp.info.1"];
        } else {
            $reply["reseller_rate"] = 100;
            $reply["reseller_mode"] = $lang["texturepack.gp.info.2"];
        }
        echo "ok";
    } else {
        echo $lang["texturepack.gp.error.2"];
    }
} else {
    echo $lang["texturepack.gp.error.1"];
}
