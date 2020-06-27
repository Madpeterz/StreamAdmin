<?php
//test
$hashcheck = sha1("".$sentunixtime."".$staticpart."".$slconfig->get_publiclinkcode()."");
if($hashcheck == $hash)
{
    $avatar_helper = new avatar_helper();
    $get_av_status = $avatar_helper->load_or_create($required_sl_values["ownerkey"],$required_sl_values["ownername"]);
    if($get_av_status == true)
    {
        $object_owner_avatar = $avatar_helper->get_avatar();
        $region_helper = new region_helper();
        $get_region_status = $region_helper->load_or_create($required_sl_values["region"]);
        if($get_region_status == true)
        {
            $region = $region_helper->get_region();

        }
        else
        {
            echo $lang["ss3.error.4"];
        }
    }
    else
    {
        echo $lang["ss3.error.5"];
    }
}
else
{
    echo $lang["ss3.error.6"];
}
?>
