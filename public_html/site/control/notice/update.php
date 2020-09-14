<?php
$input = new inputFilter();
$static_notecard = new notice_notecard();
$name = $input->postFilter("name");
$hoursremaining = $input->postFilter("hoursremaining","integer");
$immessage = $input->postFilter("immessage");
$usebot = $input->postFilter("usebot","bool");
$send_notecard = $input->postFilter("send_notecard","bool");
$notecarddetail = $input->postFilter("notecarddetail");
$notice_notecardlink = $input->postFilter("notice_notecardlink","integer");
$failed_on = "";
$redirect = "";
if(strlen($name) < 5) $failed_on .= $lang["notice.up.error.1"];
else if(strlen($name) > 100) $failed_on .= $lang["notice.up.error.2"];
else if(strlen($immessage) < 5) $failed_on .= $lang["notice.up.error.3"];
else if(strlen($immessage) > 800) $failed_on .= $lang["notice.up.error.4"];
else if(strlen($hoursremaining) < 0) $failed_on .= $lang["notice.up.error.5"];
else if(strlen($hoursremaining) > 999) $failed_on .= $lang["notice.up.error.6"];
else if($static_notecard->load($notice_notecardlink) == false) $failed_on .= $lang["notice.up.error.11"];
else if($static_notecard->get_missing() == true) $failed_on .= $lang["notice.up.error.11"];

$status = false;
if($failed_on == "")
{
    $notice = new notice();
    if($notice->load($page) == true)
    {
        $where_fields = array(array("hoursremaining"=>"="));
        $where_values = array(array($hoursremaining =>"i"));
        $count_check = $sql->basic_count($notice->get_table(),$where_fields,$where_values);
        $expected_count = 0;
        if($notice->get_hoursremaining() == $hoursremaining)
        {
            $expected_count = 1;
        }
        if($count_check["status"] == true)
        {
            if($count_check["count"] == $expected_count)
            {
                $notice->set_name($name);
                $notice->set_immessage($immessage);
                $notice->set_usebot($usebot);
                $notice->set_send_notecard($send_notecard);
                $notice->set_notecarddetail($notecarddetail);
                $notice->set_notice_notecardlink($static_notecard->get_id());
                if(in_array($page,array(6,10)) == false)
                {
                    $notice->set_hoursremaining($hoursremaining);
                }
                $update_status = $notice->save_changes();
                if($update_status["status"] == true)
                {
                    $status = true;
                    $redirect = "notice";
                    echo $lang["notice.up.info.1"];
                }
                else
                {
                    echo sprintf($lang["notice.up.error.10"],$update_status["message"]);
                }
            }
            else
            {
                echo $lang["notice.up.error.9"];
            }
        }
        else
        {
            echo $lang["notice.up.error.8"];
        }
    }
    else
    {
        echo $lang["notice.up.error.7"];
        $redirect = "notice";
    }
}
else
{
    echo $failed_on;
}
?>
