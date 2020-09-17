<?php
$input = new inputFilter();
$targetuid = $input->postFilter("targetuid");
$avatar = new avatar();
$status = false;

if($targetuid != null)
{
    $bits = explode(" ",$targetuid);
    $load_status = false;
    if(count($bits) == 2)
    {
        $firstname = strtolower($bits[0]);
        $firstname = ucfirst($firstname);
        $lastname = strtolower($bits[1]);
        $lastname = ucfirst($lastname);
        $targetuid = "".$firstname." ".$lastname."";
        $load_status = $avatar->load_by_field("avatarname",$targetuid);
    }
    else if(strlen($targetuid) == 36)
    {
        $load_status = $avatar->load_by_field("avataruuid",$targetuid);
    }
    if($load_status == true)
    {
        $rental_set = new rental_set();
        $rental_set->load_on_field("avatarlink",$avatar->get_id());
        if($rental_set->get_count() > 0)
        {
            $stream_set = new stream_set();
            $stream_set->load_ids($rental_set->get_all_by_field("streamlink"));
            if($stream_set->get_count() > 0)
            {
                $reply_dataset = array();
                foreach($rental_set->get_all_ids() as $rental_id)
                {
                    $rental = $rental_set->get_object_by_id($rental_id);
                    $stream = $stream_set->get_object_by_id($rental->get_streamlink());
                    if($stream != null)
                    {
                        $reply_dataset[] = "".$rental->get_rental_uid()."|||".$stream->get_port()."";
                    }
                }
                if(count($reply_dataset) > 0)
                {
                    $status = true;
                    $reply["dataset_count"] = count($reply_dataset);
                    $reply["dataset"] = $reply_dataset;
                    echo sprintf($lang["proxyrenew.dt.info.1"],$avatar->get_avatarname());
                }
                else
                {
                    $status = true;
                    $reply["dataset_count"] = 0;
                    echo $lang["proxyrenew.dt.error.5"];
                }
            }
            else
            {
                echo $lang["proxyrenew.dt.error.4"];
            }
        }
        else
        {
            echo $lang["proxyrenew.dt.error.3"];
        }
    }
    else
    {
        echo $lang["proxyrenew.dt.error.2"];
    }
}
else
{
    echo $lang["proxyrenew.dt.error.1"];
}
?>
