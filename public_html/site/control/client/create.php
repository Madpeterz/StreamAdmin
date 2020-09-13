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

$avatar_where_config = array(
    "fields"=>array("avatar_uid","avatarname","avataruuid"),
    "matches"=>array("=","=","="),
    "values"=>array($avataruid,$avataruid,$avataruid),
    "types"=>array("s","s","s"),
    "join_with"=>array("(OR)","(OR)")
);
$avatar_set = new avatar_set();
$avatar_set->load_with_config($avatar_where_config);

$stream_where_config = array(
    "fields"=>array("port","stream_uid"),
    "matches"=>array("=","="),
    "values"=>array($streamuid,$streamuid),
    "types"=>array("i","s"),
    "join_with"=>array("(OR)")
);
$stream_set = new stream_set();
$stream_set->load_with_config($stream_where_config);
if($stream_set->get_count() == 1)
{
    $stream = $stream_set->get_first();
}
if($avatar_set->get_count() != 1) $failed_on .= $lang["client.cr.error.5"];
else if($stream_set->get_count() != 1) $failed_on .= $lang["client.cr.error.6"];
else if($daysremaining > 999) $failed_on .= $lang["client.cr.error.3"];
else if($daysremaining < 1) $failed_on .= $lang["client.cr.error.4"];
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
    $avatar = $avatar_set->get_first();
    $unixtime = time() + ($daysremaining * $unixtime_day);
    $rental = new rental();
    $uid = $rental->create_uid("rental_uid",8,10);
    if($uid["status"] == true)
    {
        $rental->set_rental_uid($uid["uid"]);
        $rental->set_avatarlink($avatar->get_id());
        $rental->set_packagelink($stream->get_packagelink());
        $rental->set_streamlink($stream->get_id());
        $rental->set_startunixtime(time());
        $rental->set_expireunixtime($unixtime);
        $rental->set_avatarlink($avatar->get_id());
        $rental->set_noticelink($use_notice_index);
        $create_status = $rental->create_entry();
        if($create_status["status"] == true)
        {
            $stream->set_rentallink($rental->get_id());
            $stream->set_needwork(0);
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
