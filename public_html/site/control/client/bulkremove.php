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
$rental_set->load_with_config($whereconfig);
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
$package_set->load_ids($rental_set->get_all_by_field("packagelink"));
$stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
$removed_counter = 0;
$status = true;
$redirect = "client/bulkremove";
$rental_ids_removed = array();
foreach($rental_set->get_all_ids() as $rental_id)
{
    $rental = $rental_set->get_object_by_id($rental_id);
    if(strlen($rental->get_message()) == 0)
    {
        $accept = $input->postFilter("rental".$rental->get_rental_uid()."");
        if($accept == "purge")
        {
            $stream = $stream_set->get_object_by_id($rental->get_streamlink());
            if($stream != null)
            {
                $stream->set_field("rentallink",null);
                $stream->set_field("needwork",1);
                $update_status = $stream->save_changes();
                if($update_status["status"] == true)
                {
                    $all_ok = true;
                    if($slconfig->get_eventstorage() == true)
                    {
                        $package = $package_set->get_object_by_id($rental->get_packagelink());
                        $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
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
                            break;
                            $why_failed = $lang["client.br.error.1"];
                        }
                    }
                    if($all_ok == true)
                    {
                        $remove_status = $rental->remove_me();
                        if($remove_status["status"] == false)
                        {
                            $status = false;
                            echo sprintf($lang["client.br.error.2"],$rental->get_rental_uid(),$remove_status["message"]);
                            break;
                        }
                        else
                        {
                            $removed_counter++;
                        }
                    }
                }
                else
                {
                    $status = false;
                    echo sprintf($lang["client.br.error.3"],$rental->get_rental_uid(),$update_status["message"]);
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
}
if($status == true)
{
    echo sprintf($lang["client.br.info.1"],$removed_counter);
}
?>
