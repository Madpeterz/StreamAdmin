<?php
$input = new inputFilter();
$targeteventid = $input->postFilter("eventid","integer");
$event = new event();
if($event->load($targeteventid) == true)
{
    $remove_status = $event->remove_me();
    if($remove_status["status"] == true)
    {
        $reply = array("status"=>true,"message"=>"event_removed");
    }
    else
    {
        $reply = array("status"=>false,"message"=>"unable to remove event: ".$remove_status["message"]."");
    }
}
else
{
    $reply = array("status"=>false,"message"=>"unable to find event");
}
?>
