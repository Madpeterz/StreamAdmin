<?php

$input = new inputFilter();
$month = $input->getFilter("month", "integer");
$year = $input->getFilter("year", "integer");

if ($month < 1) {
    $month = 1;
} elseif ($month > 12) {
    $month = 12;
}
if ($year < 2013) {
    $year = 2013;
} elseif ($year > date("Y")) {
    $year = date("Y");
}

$start_unixtime = mktime(0, 0, 1, $month, 1, $year);
$end_month = $month + 1;
$end_year = $year;
if ($end_month > 12) {
    $end_year + 1;
    $end_month = 1;
}
$end_unixtime = mktime(0, 0, 1, $end_month, 1, $end_year);
$end_unixtime -= 5;
$view_reply->add_swap_tag_string("page_title", " In selected period - " . date("F Y", $start_unixtime));

$whereconfig = array(
    "fields" => array("unixtime","unixtime"),
    "values" => array($start_unixtime,$end_unixtime),
    "types" => array("i","i"),
    "matches" => array(">=","<="),
);

$transaction_set = new transactions_set();
$transaction_set->load_with_config($whereconfig);

$package_set = new package_set();
$region_set = new region_set();
$avatar_set = new avatar_set();

$package_set->load_ids($transaction_set->get_all_by_field("packagelink"));
$region_set->load_ids($transaction_set->get_all_by_field("regionlink"));
$avatar_set->load_ids($transaction_set->get_all_by_field("avatarlink"));

include "webpanel/view/transactions/render_list.php";
include "webpanel/view/transactions/range_form.php";
