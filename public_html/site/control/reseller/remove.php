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
            echo $lang["reseller.rm.info.1"];
        }
        else
        {
            echo sprintf($lang["reseller.rm.error.3"],$remove_status["message"]);
        }
    }
    else
    {
        echo $lang["reseller.rm.error.2"];
    }
}
else
{
    echo $lang["reseller.rm.error.1"];
    $redirect ="reseller/manage/".$page."";
}
?>
