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
        $stream = new stream();
        if($stream->load($rental->get_streamlink()) == true)
        {
            $stream->set_field("rentallink",null);
            $stream->set_field("needwork",1);
            $update_status = $stream->save_changes();
            if($update_status["status"] == true)
            {
                $package = new package();
                if($package->load($rental->get_packagelink()) == true)
                {
                    $avatar = new avatar();
                    if($avatar->load($rental->get_avatarlink()) == true)
                    {
                        $all_ok = true;
                        if($slconfig->get_eventstorage() == true)
                        {
                            $event = new event();
                            $event->set_field("avatar_uuid",$avatar->get_avataruuid());
                            $event->set_field("avatar_name",$avatar->get_avatarname());
                            $event->set_field("rental_uid",$rental->get_rental_uid());
                            $event->set_field("package_uid",$package->get_package_uid());
                            $event->set_field("event_remove",true);
                            $event->set_field("unixtime",time());
                            $event->set_field("expire_unixtime",$rental->get_expireunixtime());
                            $event->set_field("port",$stream->get_port());
                            $create_status = $event->create_entry();
                            if($create_status["status"] == false)
                            {
                                $all_ok = false;
                                echo $lang["client.rm.error.8"];
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
