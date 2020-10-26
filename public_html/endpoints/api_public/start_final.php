<?php
$reply = array();
$checkfile = "endpoints/api_public/".$required_sl_values["method"]."/".$required_sl_values["action"].".php";
if(file_exists($checkfile) == true)
{
    // $reseller, $object_owner_avatar, $owner_override, $region, $object
    $check_lang_file = "shared/lang/api_public/".$required_sl_values["method"]."/".$site_lang.".php";
    if(file_exists($check_lang_file) == true)
    {
        include($check_lang_file);
    }
    include($checkfile);
}
else
{
    $status = false;
    echo sprintf($lang["final_not_supported"],$required_sl_values["method"],$required_sl_values["action"]);
}
?>
