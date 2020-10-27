<?php
$reply = array();
$checkfile = "endpoints/api/".$required_sl_values["method"]."/".$required_sl_values["action"].".php";
if(file_exists($checkfile) == true)
{
    // $reseller, $object_owner_avatar, $owner_override, $region, $object
    $lang_file = "shared/lang/api/".$required_sl_values["method"]."/".$site_lang.".php";
    if(file_exists($lang_file) == true)
    {
        include $lang_file;
    }
    include $checkfile;
}
else
{
    $status = false;
    echo sprintf($lang["final_not_supported"],$required_sl_values["method"],$required_sl_values["action"]);
}
?>
