<?php
$input = new inputFilter();
$rate = $input->postFilter("rate","integer");
$allowed = $input->postFilter("allowed","bool");
$failed_on = "";
if($rate < 1) $failed_on .= $lang["reseller.up.error.1"];
else if($rate > 100) $failed_on .= $lang["reseller.up.error.2"];
$status = false;
$ajax_reply->set_swap_tag_string("redirect","reseller");
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
            $ajax_reply->set_swap_tag_string("message",$lang["reseller.up.info.1"]);
        }
        else
        {
            $ajax_reply->set_swap_tag_string("message",sprintf($lang["reseller.up.error.4"],$update_status["message"]));
        }
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message",$lang["reseller.up.error.3"]);
    }
}
else
{
    $status = false;
    $ajax_reply->set_swap_tag_string("message",$failed_on);
    $ajax_reply->set_swap_tag_string("redirect","");
}
?>
