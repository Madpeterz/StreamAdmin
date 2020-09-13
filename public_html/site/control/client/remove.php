<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$status = false;
$redirect ="client/manage/".$page."";
if($accept == "Accept")
{
    $rental = new rental();
    if($rental->load_by_field("rental_uid",$page) == true)
    {
        $api_requests = new api_requests_set();
        $all_ok = true;
        if($api_requests->load($rental->get_id(),"rentallink") == true)
        {
            if($api_requests->get_count() > 0)
            {
                $all_ok = false;
                echo  sprintf($lang["client.rm.error.13"],$api_requests->get_count());
            }
        }
        else
        {
            $all_ok = false;
            echo $lang["client.rm.error.12"];
        }
        if($all_ok == true)
        {
            $stream = new stream();
            if($stream->load($rental->get_streamlink()) == true)
            {
                $stream->set_rentallink(null);
                $stream->set_needwork(1);
                $update_status = $stream->save_changes();
                $server = new server();
                if($server->load($stream->get_serverlink()) == true)
                {
                    if($update_status["status"] == true)
                    {
                        $package = new package();
                        if($package->load($rental->get_packagelink()) == true)
                        {
                            $avatar = new avatar();
                            if($avatar->load($rental->get_avatarlink()) == true)
                            {
                                $all_ok = true;
                                // Event storage engine
                                if($slconfig->get_eventstorage() == true)
                                {
                                    $event = new event();
                                    $event->set_avatar_uuid($avatar->get_avataruuid());
                                    $event->set_avatar_name($avatar->get_avatarname());
                                    $event->set_rental_uid($rental->get_rental_uid());
                                    $event->set_package_uid($package->get_package_uid());
                                    $event->set_event_remove(true);
                                    $event->set_unixtime(time());
                                    $event->set_expire_unixtime($rental->get_expireunixtime());
                                    $event->set_port($stream->get_port());
                                    $create_status = $event->create_entry();
                                    if($create_status["status"] == false)
                                    {
                                        $all_ok = false;
                                        echo $lang["client.rm.error.8"];
                                    }
                                }
                                // Server API support
                                if($all_ok == true)
                                {
                                    $api = $apis_set->get_object_by_id($server->get_apilink());
                                    if($api->load($server->get_apilink()) == true)
                                    {
                                        if(($api->get_event_disable_revoke() == 1) && ($server->get_event_disable_revoke() == 1))
                                        {
                                            // create pending api request to disable stream
                                            $all_ok = create_pending_api_request($server,$stream,null,"event_disable_revoke",$lang["client.rm.error.11"]);
                                        }
                                        if($all_ok == true)
                                        {
                                            if(($api->get_event_reset_password_revoke() == 1) && ($server->get_event_reset_password_revoke() == 1))
                                            {
                                                // create pending api request to reset password
                                                $all_ok = create_pending_api_request($server,$stream,null,"event_reset_password_revoke",$lang["client.rm.error.11"]);
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $all_ok = false;
                                        echo $lang["client.rm.error.10"];
                                    }
                                }
                                if($all_ok == true)
                                {
                                    $remove_status = $rental->remove_me();
                                    if($remove_status["status"] == true)
                                    {
                                        $status = true;
                                        $redirect = "client";
                                        echo $lang["client.rm.info.1"];
                                    }
                                    else
                                    {
                                        echo sprintf($lang["client.rm.error.7"],$remove_status["message"]);
                                    }
                                }
                            }
                            else
                            {
                                echo $lang["client.rm.error.6"];
                            }
                        }
                        else
                        {
                            echo $lang["client.rm.error.5"];
                        }
                    }
                    else
                    {
                        echo $lang["client.rm.error.4"];
                    }
                }
                else
                {
                    echo $lang["client.rm.error.9"];
                }
            }
            else
            {
                echo $lang["client.rm.error.3"];
            }
        }
        else
        {
            echo $lang["client.rm.error.3"];
        }
    }
    else
    {
        echo $lang["client.rm.error.2"];
    }
}
else
{
    echo $lang["client.rm.error.1"];
}
?>
