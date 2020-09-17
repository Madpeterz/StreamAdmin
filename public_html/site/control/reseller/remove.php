<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect ="reseller";
$status = false;
if($accept == "Accept")
{
    $reseller = new reseller();
    if($reseller->load($page) == true)
    {
        $remove_status = $reseller->remove_me();
        if($remove_status["status"] == true)
        {
            $status = true;
            print $lang["reseller.rm.info.1"];
        }
        else
        {
            print sprintf($lang["reseller.rm.error.3"],$remove_status["message"]);
        }
    }
    else
    {
        print $lang["reseller.rm.error.2"];
    }
}
else
{
    print $lang["reseller.rm.error.1"];
    $redirect ="reseller/manage/".$page."";
}
?>
