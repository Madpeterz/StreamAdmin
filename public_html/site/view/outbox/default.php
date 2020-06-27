<?php
$template_parts["page_title"] .= "Status";
$services = array(
    "notecard" => array("timeper"=>30,"classname"=>"notecard_set"),
    "details" => array("timeper"=>15,"classname"=>"detail_set"),
    "mail" => array("timeper"=>15,"classname"=>"message_set"),
);
$table_head = array("Outbox name","Pending","TTC");
$table_body = array();
foreach($services as $service_name => $config)
{
    $entry = array();
    $entry[] = '<a href="[[url_base]]outbox/'.$service_name.'">'.$service_name.'</a>';
    $object_set = new $config["classname"]();
    $object_set->loadAll();
    $entry[] = $object_set->get_count();
    $time_to_clear = ($config["timeper"]*$object_set->get_count());
    if($time_to_clear > 60)
    {
        $mins = floor($time_to_clear/60);
        if($mins > 60)
        {
            $hours = floor($mins/60);
            $entry[] = $mins." hours";
        }
        else $entry[] = $mins." mins";
    }
    else $entry[] = $time_to_clear." secs";
    $table_body[] = $entry;
}
echo render_table($table_head,$table_body);
echo "<br><hr/><p>TTC is the Expected time to clear<br/>this is if the SL service object is running normaly</p>";
?>
