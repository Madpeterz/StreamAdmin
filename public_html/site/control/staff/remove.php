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
                echo $lang["staff.rm.info.1"];
            }
            else
            {
                echo sprintf($lang["staff.cr.error.10"],$remove_status["message"]);
            }
        }
        else
        {
            echo $lang["staff.rm.error.1"];
        }
    }
    else
    {
        echo $lang["staff.rm.error.1"];
    }
}
else
{
    echo $lang["staff.rm.error.1"];
    $redirect ="staff/manage/".$page."";
}
?>
