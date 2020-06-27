<?php
$event_set = new event_set();
$event_set->load_newest(1,array(),array(),"id","ASC");
if($event_set->get_count() == 1)
{
    $event = $event_set->get_first();
    $fields = $event->get_fields();
    $reply = array("status"=>true,"message"=>"event");
    foreach($fields as $field)
    {
        $reply[$field] = $event->get_field($field);
    }
}
else
{
    $reply = array("status"=>true,"message"=>"nowork");
}
?>
