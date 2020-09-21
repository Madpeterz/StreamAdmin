<?php
$table_head = array("id","Domain","Sync");
$table_body = array();
$server_set = new server_set();
$server_set->loadAll();
$apis_set = new apis_set();
$apis_set->loadAll();

foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $api = $apis_set->get_object_by_id($server->get_apilink());
    $entry = array();
    $entry[] = $server->get_id();
    $entry[] = '<a href="[[url_base]]server/manage/'.$server->get_id().'">'.$server->get_domain().'</a>';
    if(($server->get_api_sync_accounts() == true) && ($api->get_api_sync_accounts() == true))
    {
        $form = new form();
        $form->target("server/sync_accounts/".$server->get_id()."");
        $entry[] = $form->render("Sync","primary",true);
    }
    else
    {
        $entry[] = " - ";
    }
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
