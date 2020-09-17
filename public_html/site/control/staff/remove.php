<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect = "staff";
$status = false;
if($accept == "Accept")
{
    $staff = new staff();
    if($staff->load($page) == true)
    {
        if($staff->get_ownerlevel() == false)
        {
            $remove_status = $staff->remove_me();
            if($remove_status["status"] == true)
            {
                $status = true;
                print $lang["staff.rm.info.1"];
            }
            else
            {
                print sprintf($lang["staff.cr.error.10"],$remove_status["message"]);
            }
        }
        else
        {
            print $lang["staff.rm.error.1"];
        }
    }
    else
    {
        print $lang["staff.rm.error.1"];
    }
}
else
{
    print $lang["staff.rm.error.1"];
    $redirect ="staff/manage/".$page."";
}
?>
