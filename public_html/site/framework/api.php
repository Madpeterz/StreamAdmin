<?php
function create_pending_api_request(server $server,stream $stream,?rental $rental,string $eventname,string $errormessage="error: %1\$s %2\$s",bool $save_to_why_failed=false) : bool
{
    global $why_failed;
    $api_request = new api_requests();
    $api_request->set_field("serverlink",$server->get_id());
    if($rental != null ) $api_request->set_field("rentallink",$server->get_id());
    $api_request->set_field("streamlink",$stream->get_id());
    $api_request->set_field("streamlink",$stream->get_id());
    $api_request->set_field("eventname",$eventname);
    $reply = $api_request->create_entry();
    if($reply["status"] == false)
    {
        if($save_to_why_failed == true)
        {
            $why_failed = sprintf($errormessage,"event_reset_password_revoke",$reply["message"]);
        }
        else
        {
            echo sprintf($errormessage,"event_reset_password_revoke",$reply["message"]);
        }
    }
    return $reply["status"];
}
?>
