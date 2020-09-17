<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect = "avatar";
$status = false;
if($accept == "Accept")
{
    $avatar = new avatar();
    if($avatar->load_by_field("avatar_uid",$page) == true)
    {
        $remove_status = $avatar->remove_me();
        if($remove_status["status"] == true)
        {
            $status = true;
            print $lang["av.rm.info.1"];
        }
        else
        {
            print sprintf($lang["av.rm.error.3"],$remove_status["message"]);
        }
    }
    else
    {
        print $lang["av.rm.error.2"];
    }
}
else
{
    print $lang["av.rm.error.1"];
    $redirect ="avatar/manage/".$page."";
}
?>
