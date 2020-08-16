<?php
$input = new inputFilter();
$avataruuid = $input->postFilter("avataruuid");
$avatar = new avatar();
$status = false;
$reply["dataset_count"] = 0;
if($avatar->load_by_field("avataruuid",$avataruuid) == true)
{
    $banlist = new banlist();
    if($banlist->load_by_field("avatar_link",$avatar->get_id()) == false)
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
                    echo sprintf($lang["renew.dt.info.2"],$avatar->get_avatarname());
                }
                else
                {
                    $status = true;
                    echo $lang["renew.dt.error.3"];
                }
            }
            else
            {
                echo $lang["renew.dt.error2"];
            }
        }
        else
        {
            $status = true;
            echo $lang["renew.dt.info.1"];
        }
    }
    else
    {
        echo $lang["renew.dt.error2.banned"];
    }
}
else
{
    $status = true;
    echo $lang["renew.dt.error.1"];
}
?>
