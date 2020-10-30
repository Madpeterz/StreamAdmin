<?php
$server_loads = [];
foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $server_loads[$server_id] = array("ready"=>0,"sold"=>0,"needwork"=>0);
}
?>
