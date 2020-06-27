<?php
$template_parts["page_title"] .= " Templates";
$table_head = array("id","name");
$table_body = array();
$template_set = new template_set();
$template_set->loadAll();

foreach($template_set->get_all_ids() as $template_id)
{
    $tempalte = $template_set->get_object_by_id($template_id);
    $entry = array();
    $entry[] = $tempalte->get_id();
    $entry[] = '<a href="[[url_base]]template/manage/'.$tempalte->get_id().'">'.$tempalte->get_name().'</a>';
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
