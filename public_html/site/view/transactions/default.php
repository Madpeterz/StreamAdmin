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
include("site/view/transactions/render_list.php");
include("site/view/transactions/range_form.php");
?>
