<?php
$whereconfig = array(
    "fields" => array("expireunixtime"),
    "values" => array(time()),
    "types" => array("i"),
    "matches" => array("<="),
);
$input = new inputFilter();
$template_parts["page_actions"] = "";
$rental_set = new rental_set();
$stream_set = new stream_set();
$package_set = new package_set();
$avatar_set = new avatar_set();
$server_set = new server_set();
$api_requests_set = new api_requests_set();
$apis_set = new apis_set();
$rental_set->load_with_config($whereconfig);
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
$package_set->load_ids($rental_set->get_all_by_field("packagelink"));
$stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
$api_requests_set->load_ids($rental_set->get_all_by_field("id"),"rentallink");
$apis_set->loadAll();
$server_set->loadAll();
$removed_counter = 0;
$skipped_counter = 0;
$status = true;
$redirect = "client/bulkremove";
$rental_ids_removed = array();
$status = true;
foreach($rental_set->get_all_ids() as $rental_id)
{
    $rental = $rental_set->get_object_by_id($rental_id);
    if(strlen($rental->get_message()) == 0)
    {
        if($api_requests_set->get_object_by_field("rentallink",$rental_id) == null)
        {
            $accept = $input->postFilter("rental".$rental->get_rental_uid()."");
            if($accept == "purge")
            {
                $stream = $stream_set->get_object_by_id($rental->get_streamlink());
                if($stream != null)
                {
                    $server = new server();
                    if($server->load($stream->get_serverlink()) == true)
                    {
                        $stream->set_rentallink(null);
                        $stream->set_needwork(1);
                        $update_status = $stream->save_changes();
                        if($update_status["status"] == true)
                        {
                            $all_ok = true;
                            $message = "";
                            if($slconfig->get_eventstorage() == true)
                            {
                                $package = $package_set->get_object_by_id($rental->get_packagelink());
                                $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
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
                                    $message = $lang["client.br.error.1"];
                                }
                            }
                            // Server API support
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
                                $removed_counter++;
                            }
                            else
                            {
                                $status = false;
                                echo $message;
                                break;
                            }
                        }
                        else
                        {
                            $status = false;
                            echo $lang["client.rm.error.4"];
                            break;
                        }
                    }
                    else
                    {
                        $status = false;
                        echo sprintf($lang["client.br.error.5"],$rental->get_rental_uid());
                        break;
                    }
                }
                else
                {
                    $status = false;
                    echo sprintf($lang["client.br.error.4"],$rental->get_rental_uid());
                    break;
                }
            }
        }
        else
        {
            $skipped_counter++;
        }
    }
}
if($status == true)
{
    $status = true;
    $redirect = "client";
    echo sprintf($lang["client.br.info.1"],$removed_counter,$skipped_counter);
}
?>
