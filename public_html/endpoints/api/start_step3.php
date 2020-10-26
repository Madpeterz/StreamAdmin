<?php
//test
$hashcheck = sha1("".$sentunixtime."".$staticpart."".$slconfig->get_sllinkcode()."");
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
            $reseller_helper = new reseller_helper();
            $get_reseller_status = $reseller_helper->load_or_create($object_owner_avatar->get_id(),$slconfig->get_new_resellers(),$slconfig->get_new_resellers_rate());
            if($get_reseller_status == true)
            {
                $reseller = $reseller_helper->get_reseller();
                $owner_override = false;
                if($slconfig->get_owner_av() == $object_owner_avatar->get_id())
                {
                    $owner_override = true;
                }
                if(($reseller->get_allowed() == true) || ($owner_override == true))
                {
                    $object_helper = new object_helper();
                    $get_object_status = $object_helper->load_or_create($object_owner_avatar->get_id(),$region->get_id(),
                        $required_sl_values["objectuuid"],
                        $required_sl_values["objectname"],
                        $required_sl_values["objecttype"],
                        $required_sl_values["pos"],true
                    );
                    if($get_object_status == true)
                    {
                        $object = $object_helper->get_object();
                        include("endpoints/api/start_final.php");
                    }
                    else
                    {
                        echo $lang["ss3.error.1"];
                    }
                }
                else
                {
                    echo $lang["ss3.error.2"];
                }
            }
            else
            {
                echo $lang["ss3.error.3"];
            }
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
