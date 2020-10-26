<?php
$status = true;
$rentals_set = new rental_set();
$rentals_set->load_by_field("avatarlink",$object_owner_avatar->get_id());
if($rentals_set->get_count() > 0)
{
    $reply["ports"] = array();
    $reply["uids"] = array();
    $reply["states"] = array();
    $stream_set = new stream_set();
    $stream_set->load_ids($rentals_set->get_unique_array("streamlink"));
    $oneday = time() + ((60*60)*24);
    if($stream_set->get_count() > 0)
    {
        foreach($stream_set->get_all_ids() as $streamid)
        {
            $stream = $stream_set->get_object_by_id($streamid);
            $rental = $rentals_set->get_object_by_id($stream->get_rentallink());
            $reply["ports"][] = $stream->get_port();
            $reply["uids"][] = $rental->get_rental_uid();
            $timeleft = $rental->get_expireunixtime();
            if($timeleft < time())
            {
                $reply["states"][] = 0;
            }
            else if($timeleft < $oneday)
            {
                $reply["states"][] = 1;
            }
            else
            {
                $reply["states"][] = 2;
            }
        }
        echo "ok - ".count($reply["states"]);
    }
    else
    {
        echo "none";
    }
}
else
{
    echo "none";
}
?>
