<?php
$reply = array();
$checkfile = "site/api/".$required_sl_values["method"]."/".$required_sl_values["action"].".php";
if(file_exists($checkfile) == true)
{
    // $reseller, $object_owner_avatar, $owner_override, $region, $object
    include("site/lang/api/".$required_sl_values["method"]."/".$site_lang.".php");
    include($checkfile);
}
else
{
    $status = false;
    echo sprintf($lang["final_not_supported"],$required_sl_values["method"],$required_sl_values["action"]);
}
?>
