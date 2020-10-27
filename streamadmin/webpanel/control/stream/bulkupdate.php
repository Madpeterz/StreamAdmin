<?php
$whereconfig = array(
    "fields" => array("needwork","rentallink"),
    "values" => array(1,null),
    "types" => array("i","i"),
    "matches" => array("=","IS"),
);
$stream_set = new stream_set();
$stream_set->load_with_config($whereconfig);
$status = true;
$ajax_reply->set_swap_tag_string("redirect","stream/bulkupdate");
$input = new inputFilter();
$streams_updated = 0;
$streams_skipped_original_adminusername = 0;
foreach($stream_set->get_all_ids() as $stream_id)
{
    $stream = $stream_set->get_object_by_id($stream_id);
    if($stream->get_original_adminusername() == $stream->get_adminusername())
    {
        $accept = $input->postFilter("stream".$stream->get_stream_uid()."");
        if($accept == "update")
        {
            $newadminpw = $input->postFilter('stream'.$stream->get_stream_uid().'adminpw');
            $newdjpw = $input->postFilter('stream'.$stream->get_stream_uid().'djpw');
            if(($stream->get_adminpassword() != $newadminpw) && ($stream->get_djpassword() != $newdjpw))
            {
                $stream->set_adminpassword($newadminpw);
                $stream->set_djpassword($newdjpw);
                $stream->set_needwork(0);
                $update_status = $stream->save_changes();
                if($update_status["status"] == false)
                {
                    echo sprintf($lang["stream.bu.error.1"],$update_status["message"]);
                    $status = false;
                    break;
                }
                else
                {
                    $streams_updated++;
                }
            }
        }
    }
    else
    {
        $streams_skipped_original_adminusername++;
    }
}
if($status == true)
{
    if($streams_skipped_original_adminusername > 0)
    {
        $ajax_reply->set_swap_tag_string("message",sprintf($lang["stream.bu.info.1"],$streams_updated,$streams_skipped_original_adminusername));
    }
    else
    {
        $ajax_reply->set_swap_tag_string("message",sprintf($lang["stream.bu.info.1"],$streams_updated));
    }
}
?>
