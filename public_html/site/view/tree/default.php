<?php
$table_head = array("ID","ID - Name");
$table_body = array();
$treevender_set = new treevender_set();
$treevender_set->loadAll();

foreach($treevender_set->get_all_ids() as $treevender_id)
{
    $treevender = $treevender_set->get_object_by_id($treevender_id);
    $entry = array();
    $entry[] = $treevender->get_id();
    $entry[] = ''.$treevender->get_id().' - <a href="[[url_base]]tree/manage/'.$treevender->get_id().'">'.$treevender->get_name().'</a>';
    $table_body[] = $entry;
}
print render_datatable($table_head,$table_body);
?>
