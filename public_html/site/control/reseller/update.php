<?php
$input = new inputFilter();
$rate = $input->postFilter("rate","integer");
$allowed = $input->postFilter("allowed","bool");
$failed_on = "";
if($rate < 1) $failed_on .= $lang["reseller.up.error.1"];
else if($rate > 100) $failed_on .= $lang["reseller.up.error.2"];
$status = false;
$redirect = "reseller";
if($failed_on == "")
{
    $reseller = new reseller();
    if($reseller->load($page) == true)
    {
        $reseller->set_rate($rate);
        $reseller->set_allowed($allowed);
        $update_status = $reseller->save_changes();
        if($update_status["status"] == true)
        {
            $status = true;
            echo $lang["reseller.up.info.1"];
        }
        else
        {
            echo sprintf($lang["reseller.up.error.4"],$update_status["message"]);
        }
    }
    else
    {
        echo $lang["reseller.up.error.3"];
    }
}
else
{
    $status = false;
    $redirect = "";
    echo $failed_on;
}
?>
