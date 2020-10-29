<?php
$table_head = array("id","Domain");
$table_body = array();
$server_set = new server_set();
$server_set->loadAll();
$apis_set = new apis_set();
$apis_set->loadAll();
$has_api_sync = false;
foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    if($server->get_api_sync_accounts() == true)
    {
        $has_api_sync = true;
        $table_head = array("id","Domain","Last sync","Sync");
        break;
    }
}
foreach($server_set->get_all_ids() as $server_id)
{
    $server = $server_set->get_object_by_id($server_id);
    $api = $apis_set->get_object_by_id($server->get_apilink());
    $entry = array();
    $entry[] = $server->get_id();
    $entry[] = '<a href="[[url_base]]server/manage/'.$server->get_id().'">'.$server->get_domain().'</a>';
    if($has_api_sync == true)
    {
        if(($server->get_api_sync_accounts() == true) && ($api->get_api_sync_accounts() == true))
        {
            $form = new form();
            $form->target("server/sync_accounts/".$server->get_id()."");
            $entry[] = expired_ago($server->get_last_api_sync());
            $entry[] = $form->render("Sync","primary",true,true);
        }
        else
        {
            $entry[] = " - ";
            $entry[] = " - ";
        }
    }
    $table_body[] = $entry;
}
$view_reply->set_swap_tag_string("page_content",render_datatable($table_head,$table_body));
?>
