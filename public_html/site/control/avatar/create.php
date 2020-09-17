<?php
$avatar = new avatar();
$input = new inputFilter();
$avatarname = $input->postFilter("avatarname");
$avataruuid = $input->postFilter("avataruuid");
$failed_on = "";
if(count(explode(" ",$avatarname)) == 1) $avatarname .= " Resident";
if(strlen($avatarname) < 5) $failed_on .= $lang["av.cr.error.1"];
else if(strlen($avatarname) > 125) $failed_on .= $lang["av.cr.error.2"];
else if(strlen($avataruuid) != 36) $failed_on .= $lang["av.cr.error.3"];
else if($avatar->load_by_field("avataruuid",$avataruuid) == true) $failed_on .= $lang["av.cr.error.4"];
$status = false;
if($failed_on == "")
{
    $avatar_helper = new avatar_helper();
    $status = $avatar_helper->load_or_create($avataruuid,$avatarname);
    if($status == true)
    {
        $status = true;
        print $lang["av.cr.info.1"];
        $redirect = "avatar";
    }
    else
    {
        print $lang["av.cr.error.5"];
    }
}
else
{
    print $failed_on;
}
?>
