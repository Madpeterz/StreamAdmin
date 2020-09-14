<?php
$server_set = new server_set();
$server_set->loadAll();
$apis_set = new apis_set();
$apis_set->loadAll();
function process_notice_change(notice $notice)
{
    global $reply, $apis_set, $server_set, $slconfig, $changes, $why_failed, $all_ok, $bot_helper, $swapables_helper, $rental, $botconfig, $botavatar, $avatar_set, $stream_set, $package_set, $server_set, $lang;
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
        $rental->set_noticelink($notice->get_id());
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
                // Event storage engine
                if($slconfig->get_eventstorage() == true)
                {
                    $event = new event();
                    $event->set_avatar_uuid($avatar->get_avataruuid());
                    $event->set_avatar_name($avatar->get_avatarname());
                    $event->set_rental_uid($rental->get_rental_uid());
                    $event->set_package_uid($package->get_package_uid());
                    $event->set_event_expire(true);
                    $event->set_unixtime(time());
                    $event->set_expire_unixtime($rental->get_expireunixtime());
                    $event->set_port($stream->get_port());
                    $create_status = $event->create_entry();
                    if($create_status["status"] == false)
                    {
                        $all_ok = false;
                        $why_failed = $lang["noticeserver.n.error.5"];
                    }
                }
                if($all_ok == true)
                {
                    include("site/api_serverlogic/expire.php");
                    $all_ok = $api_serverlogic_reply;
                }
            }
            if($all_ok == true)
            {
                if($notice->get_send_notecard() == true)
                {
                    if($botconfig->get_notecards() == true)
                    {
                        $notecard = new notecard();
                        $notecard->set_rentallink($rental->get_id());
                        $notecard->set_as_notice(1);
                        $notecard->set_noticelink($notice->get_id());
                        $create_status = $notecard->create_entry();
                        if($create_status["status"] == false)
                        {
                            $all_ok = false;
                            $why_failed = sprintf($lang["noticeserver.n.error.7"],$create_status["message"]);
                        }
                    }
                }
            }
            if($all_ok == true)
            {
                $notice_notecard = new notice_notecard();
                if($notice_notecard->load($notice->get_notice_notecardlink()) == true)
                {
                    if($notice_notecard->get_missing() == false)
                    {
                        $reply["send_static_notecard"] = $notice_notecard->get_name();
                        $reply["send_static_notecard_to"] = $avatar->get_avataruuid();
                    }
                }
                else
                {
                    $all_ok = false;
                    $why_failed = "Unable to find notice card";
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
                                    break;
                                }
                            }
                        }
                    }
                    else
                    {
                        process_notice_change($expired_notice);
                        break;
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
