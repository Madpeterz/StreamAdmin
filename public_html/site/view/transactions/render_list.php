<?php
$table_head = array("id","Transaction UID","Client","Package","Region","Amount","Datetime","Mode");
$table_body = array();
foreach($transaction_set->get_all_ids() as $transaction_id)
{
    $transaction = $transaction_set->get_object_by_id($transaction_id);
    $packagename = "";
    if($transaction->get_packagelink() != null)
    {
        $package = $package_set->get_object_by_id($transaction->get_packagelink());
        $packagename = $package->get_name();
    }
    $regionname = "";
    if($transaction->get_regionlink() != null)
    {
        $region = $region_set->get_object_by_id($transaction->get_regionlink());
        $regionname = $region->get_name();
    }


    $avatar = $avatar_set->get_object_by_id($transaction->get_avatarlink());
    $entry = array();
    $entry[] = $transaction->get_id();
    $entry[] = $transaction->get_transaction_uid();
    $entry[] = $avatar->get_avatarname();
    $entry[] = $packagename;
    $entry[] = $regionname;
    $entry[] = $transaction->get_amount();
    $entry[] = date('l jS \of F Y h:i:s A',$transaction->get_unixtime());
    if($transaction->get_renew() == 1) $entry[] = "Renew";
    else $entry[] = "New";
    $table_body[] = $entry;
}
echo render_datatable($table_head,$table_body);
?>
