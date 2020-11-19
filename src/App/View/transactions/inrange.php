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
$this->output->addSwapTagString("page_title", " In selected period - " . date("F Y", $start_unixtime));

$whereconfig = [
    "fields" => ["unixtime","unixtime"],
    "values" => [$start_unixtime,$end_unixtime],
    "types" => ["i","i"],
    "matches" => [">=","<="],
];

$transaction_set = new transactions_set();
$transaction_set->loadWithConfig($whereconfig);

$package_set = new package_set();
$region_set = new region_set();
$avatar_set = new avatar_set();

$package_set->loadIds($transaction_set->getAllByField("packagelink"));
$region_set->loadIds($transaction_set->getAllByField("regionlink"));
$avatar_set->loadIds($transaction_set->getAllByField("avatarlink"));

include "webpanel/view/transactions/render_list.php";
include "webpanel/view/transactions/range_form.php";
