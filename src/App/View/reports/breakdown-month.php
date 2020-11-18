<?php

$input = new inputFilter();
$year = $input->getFilter("year", "integer");
if ($year < 2013) {
    $year = 2013;
} elseif ($year > date("Y")) {
    $year = date("Y");
}

$months = [1 => "Jan",2 => "Feb",3 => "Mar",4 => "Apr",5 => "May",6 => "June",7 => "July",8 => "Aug",9 => "Sep",10 => "Oct",11 => "Nov",12 => "Dec"];
$month = $input->getFilter("month", "integer");
if ($month < 1) {
    $month = 1;
} elseif ($month > 12) {
    $month = 12;
}

$this->output->addSwapTagString("page_title", "Month breakdown: " . $months[$month] . " / " . $year);

$transactions_set = new transactions_set();
$whereconfig = [
    "fields" => ["unixtime","unixtime"],
    "values" => [mktime(0, 0, 1, $month, 1, $year),mktime(23, 59, 59, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year), $year)],
    "types" => ["i","i"],
    "matches" => [">=","<="],
];
$transactions_set->load_with_config($whereconfig);

// totals
$new_rentals = 0;
$renewed_rentals = 0;
$amount_new = 0;
$amount_renew = 0;

$lookups = [
    mktime(23, 59, 59, $month, 7, $year) => 1,
    mktime(23, 59, 59, $month, 14, $year) => 2,
    mktime(23, 59, 59, $month, 21, $year) => 3,
    mktime(23, 59, 59, $month, 28, $year) => 4,
    mktime(23, 59, 59, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year), $year) => 5,
];

$month_datasets = [
    1 => ["title" => "Week 1","new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0],
    2 => ["title" => "Week 2","new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0],
    3 => ["title" => "Week 3","new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0],
    4 => ["title" => "Week 4","new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0],
    5 => ["title" => "Week 5","new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0],
];


foreach ($transactions_set->getAllIds() as $transaction_id) {
    $transaction = $transactions_set->getObjectByID($transaction_id);
    $month_id = 1;
    foreach ($lookups as $max_unixtime => $month_num) {
        if ($transaction->get_unixtime() < $max_unixtime) {
            $month_id = $month_num;
            break;
        }
    }
    $month_datasets[$month_id]["sum"] += $transaction->get_amount();
    $month_datasets[$month_id]["counted"] += 1;
    if ($transaction->get_renew() == 1) {
        $month_datasets[$month_id]["renew"] += 1;
        $month_datasets[$month_id]["amount_renew"] += $transaction->get_amount();
        $renewed_rentals++;
        $amount_renew += $transaction->get_amount();
    } else {
        $month_datasets[$month_id]["new"] += 1;
        $month_datasets[$month_id]["amount_new"] += $transaction->get_amount();
        $new_rentals++;
        $amount_new += $transaction->get_amount();
    }
}


$last_month = ["title" => "None","new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0];
$best_month = ["title" => "None","new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0];

$best_month_total = 0;
foreach ($month_datasets as $index => $dataset) {
    $sum = $dataset["amount_new"] + $dataset["amount_renew"];
    if ($sum > $best_month_total) {
        $best_month = $dataset;
        $best_month_total = $sum;
    }
}
function amount_changed($old, $new)
{
    if ($old == 0) {
        if ($new != 0) {
            return "<span class=\"reports-gain\">100 %</span>";
        } else {
            return " / ";
        }
    } else {
        if ($new == 0) {
            return "<span class=\"reports-loss\">100 %</span>";
        } else {
            $change = number_format((1 - $old / $new) * 100, 2);
            if ($change > 0) {
                return "<span class=\"reports-gain\">" . $change . " %</span>";
            } else {
                return "<span class=\"reports-loss\">" . $change . " %</span>";
            }
        }
    }
}
$table_head = ["Month","L$ total","Transactions total","Count / New","Count / Renew","L$ / total new","L$ total renew","Change from last week","Change from best week"];
$table_body = [];
foreach ($month_datasets as $index => $dataset) {
    $entry = [];
    if ($dataset["sum"] > 0) {
        $entry[] = $dataset["title"];
        $entry[] = $dataset["sum"];
        $entry[] = $dataset["counted"];
        $entry[] = $dataset["new"];
        $entry[] = $dataset["renew"];
        $entry[] = $dataset["amount_new"];
        $entry[] = $dataset["amount_renew"];
        if ($last_month["title"] == "None") {
            $entry[] = "-";
        } else {
            $entry[] = amount_changed($last_month["sum"], $dataset["sum"]);
        }
        if ($dataset["title"] != $best_month["title"]) {
            $entry[] = amount_changed($best_month["sum"], $dataset["sum"]);
        } else {
            $entry[] = " / ";
        }
        $last_month = $dataset;
        if ($dataset["title"] == $best_month["title"]) {
            $new_entry = [];
            $counter = 0;
            foreach ($entry as $point) {
                if ($counter < 7) {
                    $new_entry[] = "<span class=\"reports-best\">" . $point . "</span>";
                } else {
                    $new_entry[] = $point;
                }
                $counter++;
            }
            $entry = $new_entry;
        }
        $table_body[] = $entry;
    }
}
$pages = [];
$pages["Fast report"] = render_table(["New","Renews","L$ total [New]","L$ total [Rewew]"], [[$new_rentals,$renewed_rentals,$amount_new,$amount_renew]]);
$pages["Month breakdown"] = render_table($table_head, $table_body);
$paged_info = new paged_info();
$this->output->setSwapTagString("page_content", $paged_info->render($pages));
