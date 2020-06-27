<?php
function process_notice_change(notice $notice)
{
    global $slconfig, $changes, $why_failed, $all_ok, $bot_helper, $swapables_helper, $rental, $botconfig, $botavatar, $avatar_set, $stream_set, $package_set, $server_set, $lang;
    $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
    $stream = $stream_set->get_object_by_id($rental->get_streamlink());
    $package = $package_set->get_object_by_id($stream->get_packagelink());
    $server = $server_set->get_object_by_id($stream->get_serverlink());
    $sendmessage = $swapables_helper->get_swapped_text($notice->get_immessage(),$avatar,$rental,$package,$server,$stream);
    $send_message_status = $bot_helper->send_message($botconfig,$botavatar,$avatar,$sendmessage,$notice->get_usebot());
    if($send_message_status["status"] == false)
    {
        $all_ok = false;
        $why_failed = $send_message_status["message"];
    }
    else
    {
        $rental->set_field("noticelink",$notice->get_id());
        $save_status = $rental->save_changes();
        if($save_status["status"] == false)
        {
            $all_ok = false;
            $why_failed = $save_status["message"];
        }
        else
        {
            if($notice->get_hoursremaining() == 0)
            {
                if($slconfig->get_eventstorage() == true)
                {
                    $event = new event();
                    $event->set_field("avatar_uuid",$avatar->get_avataruuid());
                    $event->set_field("avatar_name",$avatar->get_avatarname());
                    $event->set_field("rental_uid",$rental->get_rental_uid());
                    $event->set_field("package_uid",$package->get_package_uid());
                    $event->set_field("event_expire",true);
                    $event->set_field("unixtime",time());
                    $event->set_field("expire_unixtime",$rental->get_expireunixtime());
                    $event->set_field("port",$stream->get_port());
                    $create_status = $event->create_entry();
                    if($create_status["status"] == false)
                    {
                        $all_ok = false;
                        $why_failed = $lang["noticeserver.n.error.5"];
                    }
                }
            }
            if($all_ok == true)
            {
                $changes++;
            }
        }
    }
}

$rental_ids_expired = array();

$status = true;
$why_failed = "";
$all_ok = true;
$changes = 0;
if($owner_override == true)
{
    $bot_helper = new bot_helper();
    $swapables_helper = new swapables_helper();

    $notice_set = new notice_set();
    $notice_set->loadAll();
    $sorted_linked = $notice_set->get_linked_array("hoursremaining","id");
    ksort($sorted_linked,SORT_NUMERIC);
    $max_hours = array_keys($sorted_linked)[count($sorted_linked)-2]; // ignore 999 hours at the end for active
    $unixtime = $max_hours * $unixtime_hour;
    $expired_notice = $notice_set->get_object_by_field("hoursremaining",0);

    $where_config = array(
        "fields" => array("expireunixtime","noticelink"),
        "values" => array((time()+$unixtime),$expired_notice->get_id()),
        "types" => array("i","i"),
        "matches" => array("<=","!=")
    );

    $rental_set = new rental_set();
    $rental_set->load_with_config($where_config);
    $avatar_set = new avatar_set();
    $avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
    $botconfig = new botconfig();

    if($botconfig->load(1) == true)
    {
        $botavatar = null;
        if($botconfig->get_avatarlink() > 0)
        {
            $botavatar = new avatar();
            if($botavatar->load($botconfig->get_avatarlink()) == true)
            {
                $stream_set = new stream_set();
                $stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
                $server_set = new server_set();
                $server_set->load_ids($stream_set->get_all_by_field("serverlink"));
                $package_set = new package_set();
                $package_set->load_ids($stream_set->get_all_by_field("packagelink"));
                $rental = null;
                foreach($rental_set->get_all_ids() as $rental_id)
                {
                    $rental = $rental_set->get_object_by_id($rental_id);
                    if($rental->get_expireunixtime() > time())
                    {
                        $hours_remain = ceil(($rental->get_expireunixtime() - time())/$unixtime_hour);
                        if($hours_remain > 0)
                        {
                            $current_notice_level = $notice_set->get_object_by_id($rental->get_noticelink());
                            $current_hold_hours = $current_notice_level->get_hoursremaining();
                            $use_notice_index = 0;
                            foreach($sorted_linked as $hours => $index)
                            {
                                if(($hours > 0) && ($hours < 999))
                                {
                                    if($hours > $hours_remain)
                                    {
                                        if($hours < $current_hold_hours)
                                        {
                                            $use_notice_index = $index;
                                        }
                                        break;
                                    }
                                }
                            }

                            if($use_notice_index != 0)
                            {
                                if($use_notice_index != $current_notice_level->get_id())
                                {
                                    $notice = $notice_set->get_object_by_id($use_notice_index);
                                    process_notice_change($notice);
                                    if($all_ok == false) break;
                                }
                            }
                        }
                    }
                    else
                    {
                        process_notice_change($expired_notice);
                    }
                }
                if($all_ok == false)
                {
                    echo sprintf($lang["noticeserver.n.error.6"],$why_failed);
                }
                else
                {
                    $status = true;
                    echo sprintf($lang["noticeserver.n.info.1"],$changes);
                }
            }
            else
            {
                echo $lang["noticeserver.n.error.4"];
            }
        }
        else
        {
            echo $lang["noticeserver.n.error.3"];
        }
    }
    else
    {
        echo $lang["noticeserver.n.error.2"];
    }
}
else
{
    echo $lang["noticeserver.n.error.1"];
}
?>