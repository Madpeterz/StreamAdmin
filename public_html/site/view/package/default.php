<?php
$template_parts["page_title"] .= "All";
$package_set = new package_set();
$package_set->loadAll();

$table_head = array("id","UID","Name","Listeners","Days","Kbps","Cost");
$table_body = array();

foreach($package_set->get_all_ids() as $package_id)
{
    $package = $package_set->get_object_by_id($package_id);
    $entry = array();
    $entry[] = $package->get_id();
    $entry[] = '<a href="[[url_base]]package/manage/'.$package->get_package_uid().'">'.$package->get_package_uid().'</a>';
    $entry[] = $package->get_name();
    $entry[] = $package->get_listeners();
    $entry[] = $package->get_days();
    $entry[] = $package->get_bitrate();
    $entry[] = $package->get_cost();
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
