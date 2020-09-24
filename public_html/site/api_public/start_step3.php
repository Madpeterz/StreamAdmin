<?php
//test
$hashcheck = sha1("".$sentunixtime."".$staticpart."".$slconfig->get_publiclinkcode()."");
if($hashcheck == $hash)
{
    $raw = "".$sentunixtime."".$required_sl_values["ownerkey"]."".$slconfig->get_publiclinkcode()."";
    $ownerhashcheck = sha1($raw);
    if($ownerhashcheck == $ownerhash)
    {
        $avatar_helper = new avatar_helper();
        $get_av_status = $avatar_helper->load_or_create($required_sl_values["ownerkey"],$required_sl_values["ownername"]);
        if($get_av_status == true)
        {
            $object_owner_avatar = $avatar_helper->get_avatar();
            include("site/api_public/start_final.php");
        }
        else
        {
            echo $lang["ss3.error.5"];
        }
    }
    else
    {
        echo $lang["ss3.error.6"]." ownercheck";
    }
}
else
{
    echo $lang["ss3.error.6"];
}
?>
