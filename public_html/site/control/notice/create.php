<?php
$notice = new notice();
$input = new inputFilter();
$name = $input->postFilter("name");
$hoursremaining = $input->postFilter("hoursremaining","integer");
$immessage = $input->postFilter("immessage");
$usebot = $input->postFilter("usebot","bool");
$send_notecard = $input->postFilter("send_notecard","bool");
$notecarddetail = $input->postFilter("notecarddetail");

$failed_on = "";
$redirect = "";
if(strlen($name) < 5) $failed_on .= $lang["notice.cr.error.1"];
else if(strlen($name) > 100) $failed_on .= $lang["notice.cr.error.2"];
else if(strlen($immessage) < 5) $failed_on .= $lang["notice.cr.error.3"];
else if(strlen($immessage) > 800) $failed_on .= $lang["notice.cr.error.4"];
else if(strlen($hoursremaining) < 0) $failed_on .= $lang["notice.cr.error.5"];
else if(strlen($hoursremaining) > 999) $failed_on .= $lang["notice.cr.error.6"];
else if($notice->load_by_field("hoursremaining",$hoursremaining) == true) $failed_on .= $lang["notice.cr.error.7"];
if($failed_on == "")
{
    $notice = new notice();
    $notice->set_name($name);
    $notice->set_immessage($immessage);
    $notice->set_usebot($usebot);
    $notice->set_hoursremaining($hoursremaining);
    $notice->set_send_notecard($send_notecard);
    $notice->set_notecarddetail($notecarddetail);
    $create_status = $notice->create_entry();
    if($create_status["status"] == true)
    {
        $status = true;
        $redirect = "notice";
        echo $lang["notice.cr.info.1"];
    }
    else
    {
        $status = false;
        echo sprintf($lang["notice.cr.error.8"],$create_status["message"]);
    }
}
else
{
    $status = false;
    echo $failed_on;
}
?>
