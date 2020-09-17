<?php
$table_head = array("id","Domain");
$table_body = array();
$server_set = new server_set();
$server_set->loadAll();

foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $entry = array();
    $entry[] = $server->get_id();
    $entry[] = '<a href="[[url_base]]server/manage/'.$server->get_id().'">'.$server->get_domain().'</a>';
    $table_body[] = $entry;
}
print render_datatable($table_head,$table_body);
?>
