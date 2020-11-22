<?php

$table_head = ["id","Transaction UID","Client","Package","Region","Amount","Datetime","Mode"];
if ($session->getOwnerLevel() == 1) {
    $table_head[] = "Remove";
}
$table_body = [];
foreach ($transaction_set->getAllIds() as $transaction_id) {
    $transaction = $transaction_set->getObjectByID($transaction_id);
    $packagename = "";
    if ($transaction->getPackagelink() != null) {
        $package = $package_set->getObjectByID($transaction->getPackagelink());
        $packagename = $package->getName();
    }
    $regionname = "";
    if ($transaction->get_regionlink() != null) {
        $region = $region_set->getObjectByID($transaction->get_regionlink());
        $regionname = $region->getName();
    }


    $avatar = $avatar_set->getObjectByID($transaction->getAvatarlink());
    $entry = [];
    $entry[] = $transaction->getId();
    $entry[] = $transaction->get_transaction_uid();
    $entry[] = $avatar->getAvatarname();
    $entry[] = $packagename;
    $entry[] = $regionname;
    $entry[] = $transaction->getAmount();
    $entry[] = date('l jS \of F Y h:i:s A', $transaction->getUnixtime());
    if ($transaction->getRenew() == 1) {
        $entry[] = "Renew";
    } else {
        $entry[] = "New";
    }
    if ($session->getOwnerLevel() == 1) {
        $entry[] = "<a href=\"[[url_base]]transactions/remove/" . $transaction->get_transaction_uid() . "\"><button type=\"button\" class=\"btn btn-danger btn-sm\"><i class=\"fas fa-minus-circle\"></i></button></a>";
    }
    $table_body[] = $entry;
}
$this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
