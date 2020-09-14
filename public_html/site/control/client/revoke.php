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
        if($api_requests->load_by_field($rental->get_id(),"rentallink") == true)
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
                                $message = "";
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
                                        $message = $lang["client.rm.error.8"];
                                    }
                                }
                                if($all_ok == true)
                                {
                                    $remove_status = $rental->remove_me();
                                    $all_ok = $remove_status["status"];
                                    if($remove_status["status"] == true)
                                    {
                                        $status = true;
                                        $redirect = "client";
                                        $message = $lang["client.rm.info.1"];
                                    }
                                    else
                                    {
                                        $message = sprintf($lang["client.rm.error.7"],$remove_status["message"]);
                                    }
                                }
                                if($all_ok == true)
                                {
                                    $rental = null;
                                    include("site/api_serverlogic/revoke.php");
                                    $all_ok = $api_serverlogic_reply;
                                    if($status != true)
                                    {
                                        $message = $why_failed;
                                    }
                                }
                                if($all_ok == true)
                                {
                                    $status = true;
                                    $redirect = "client";
                                    echo $lang["client.rm.info.1"];
                                }
                                else
                                {
                                    echo $message;
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