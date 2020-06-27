<?php
$transaction_set = new transactions_set();
$transaction_set->load_newest(30);

$package_set = new package_set();
$region_set = new region_set();
$avatar_set = new avatar_set();

$package_set->load_ids($transaction_set->get_all_by_field("packagelink"));
$region_set->load_ids($transaction_set->get_all_by_field("regionlink"));
$avatar_set->load_ids($transaction_set->get_all_by_field("avatarlink"));


$template_parts["page_title"] .= " Newest 30";
$table_head = array("id","Transaction UID","Client","Package","Region","Amount","Datetime");
$table_body = array();
foreach($transaction_set->get_all_ids() as $transaction_id)
{
    $transaction = $transaction_set->get_object_by_id($transaction_id);
    $package = $package_set->get_object_by_id($transaction->get_packagelink());
    $region = $region_set->get_object_by_id($transaction->get_regionlink());
    $avatar = $avatar_set->get_object_by_id($transaction->get_avatarlink());
    $entry = array();
    $entry[] = $transaction->get_id();
    $entry[] = $transaction->get_transaction_uid();
    $entry[] = $avatar->get_avatarname();
    $entry[] = $package->get_name();
    $entry[] = $region->get_name();
    $entry[] = $transaction->get_amount();
    $entry[] = date('l jS \of F Y h:i:s A',$transaction->get_unixtime());
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
