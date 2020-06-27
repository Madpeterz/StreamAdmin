<?php
$avatar = new avatar();
$stream = new stream();
$input = new inputFilter();
$avataruid = $input->postFilter("avataruid");
$streamuid = $input->postFilter("streamuid");
$daysremaining = $input->postFilter("daysremaining","integer");
$status = false;
$redirect = "client";
$failed_on = "";
if(strlen($avataruid) != 8) $failed_on .= $lang["client.cr.error.1"];
else if(strlen($streamuid) != 8) $failed_on .= $lang["client.cr.error.2"];
else if($daysremaining > 999) $failed_on .= $lang["client.cr.error.3"];
else if($daysremaining < 1) $failed_on .= $lang["client.cr.error.4"];
else if($avatar->load_by_field("avatar_uid",$avataruid) == false) $failed_on .= $lang["client.cr.error.5"];
else if($stream->load_by_field("stream_uid",$streamuid) == false) $failed_on .= $lang["client.cr.error.6"];
else if($stream->get_rentallink() > 0) $failed_on .= $lang["client.cr.error.7"];
$notice_set = new notice_set();
$notice_set->loadAll();
$sorted_linked = $notice_set->get_linked_array("hoursremaining","id");
ksort($sorted_linked,SORT_NUMERIC);
$hours_remain = $daysremaining * 24;
$use_notice_index = 0;
foreach($sorted_linked as $hours => $index)
{
    if($hours > $hours_remain) break;
    else
    {
        $use_notice_index = $index;
    }
}
if($failed_on == "")
{
    $unixtime = time() + ($daysremaining * $unixtime_day);
    $rental = new rental();
    $uid = $rental->create_uid("rental_uid",8,10);
    if($uid["status"] == true)
    {
        $rental->set_field("rental_uid",$uid["uid"]);
        $rental->set_field("avatarlink",$avatar->get_id());
        $rental->set_field("packagelink",$stream->get_packagelink());
        $rental->set_field("streamlink",$stream->get_id());
        $rental->set_field("startunixtime",time());
        $rental->set_field("expireunixtime",$unixtime);
        $rental->set_field("avatarlink",$avatar->get_id());
        $rental->set_field("noticelink",$use_notice_index);
        $create_status = $rental->create_entry();
        if($create_status["status"] == true)
        {
            $stream->set_field("rentallink",$rental->get_id());
            $update_status = $stream->save_changes();
            if($update_status["status"] == true)
            {
                $status = true;
                echo $lang["client.cr.info.1"];
            }
            else
            {
                echo $lang["client.cr.error.10"];
            }
        }
        else
        {
            echo sprintf($lang["client.cr.error.9"],$create_status["message"]);
        }
    }
    else
    {
        echo $lang["client.cr.error.8"];
    }
}
else
{
    $redirect = "";
    echo $failed_on;
}
?>
