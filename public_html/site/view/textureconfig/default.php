<?php
$template_parts["page_title"] .= " Texture packs";
$table_head = array("id","ID","name");
$table_body = array();
$textureconfig_set = new textureconfig_set();
$textureconfig_set->loadAll();

foreach($textureconfig_set->get_all_ids() as $textureconfig_id)
{
    $textureconfig = $textureconfig_set->get_object_by_id($textureconfig_id);
    $entry = array();
    $entry[] = $textureconfig->get_id();
    $entry[] = $textureconfig->get_id();
    $entry[] = '<a href="[[url_base]]textureconfig/manage/'.$textureconfig->get_id().'">'.$textureconfig->get_name().'</a>';
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
