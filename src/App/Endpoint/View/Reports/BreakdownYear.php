<?php

namespace App\Endpoint\View\Reports;

use App\Models\Sets\TransactionsSet;
use App\Template\PagedInfo;

class BreakdownYear extends View
{
    public function process(): void
    {

        $year = $input->getFilter("year", "integer");
        if ($year > date("Y")) {
            $year = date("Y");
        }
        if ($year < 2013) {
            $year = 2013;
        }

        $this->output->addSwapTagString("page_title", "Year breakdown: " . $year);

        $transactions_set = new TransactionsSet();
        $whereconfig = [
        "fields" => ["unixtime","unixtime"],
        "values" => [
            mktime(0, 0, 1, 1, 1, $year),
            mktime(23, 59, 59, 12, cal_days_in_month(CAL_GREGORIAN, 12, $year), $year),
        ],
        "types" => ["i","i"],
        "matches" => [">=","<="],
        ];
        $transactions_set->loadWithConfig($whereconfig);

    // totals
        $new_rentals = 0;
        $renewed_rentals = 0;
        $amount_new = 0;
        $amount_renew = 0;

        $lookups = [
        mktime(23, 59, 59, 1, cal_days_in_month(CAL_GREGORIAN, 1, $year), $year) => 1,
        mktime(23, 59, 59, 2, cal_days_in_month(CAL_GREGORIAN, 2, $year), $year) => 2,
        mktime(23, 59, 59, 3, cal_days_in_month(CAL_GREGORIAN, 3, $year), $year) => 3,
        mktime(23, 59, 59, 4, cal_days_in_month(CAL_GREGORIAN, 4, $year), $year) => 4,
        mktime(23, 59, 59, 5, cal_days_in_month(CAL_GREGORIAN, 5, $year), $year) => 5,
        mktime(23, 59, 59, 6, cal_days_in_month(CAL_GREGORIAN, 6, $year), $year) => 6,
        mktime(23, 59, 59, 7, cal_days_in_month(CAL_GREGORIAN, 7, $year), $year) => 7,
        mktime(23, 59, 59, 8, cal_days_in_month(CAL_GREGORIAN, 8, $year), $year) => 8,
        mktime(23, 59, 59, 9, cal_days_in_month(CAL_GREGORIAN, 9, $year), $year) => 9,
        mktime(23, 59, 59, 10, cal_days_in_month(CAL_GREGORIAN, 10, $year), $year) => 10,
        mktime(23, 59, 59, 11, cal_days_in_month(CAL_GREGORIAN, 11, $year), $year) => 11,
        mktime(23, 59, 59, 12, cal_days_in_month(CAL_GREGORIAN, 12, $year), $year) => 12,
        ];
        $defaults_month = ["new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0];
        $month_datasets = [
            1 => array_merge($defaults_month, ["title" => "Jan"]),
            2 => array_merge($defaults_month, ["title" => "Feb"]),
            3 => array_merge($defaults_month, ["title" => "Mar"]),
            4 => array_merge($defaults_month, ["title" => "Apr"]),
            5 => array_merge($defaults_month, ["title" => "May"]),
            6 => array_merge($defaults_month, ["title" => "June"]),
            7 => array_merge($defaults_month, ["title" => "July"]),
            8 => array_merge($defaults_month, ["title" => "Aug"]),
            9 => array_merge($defaults_month, ["title" => "Sep"]),
            10 => array_merge($defaults_month, ["title" => "Oct"]),
            11 => array_merge($defaults_month, ["title" => "Nov"]),
            12 => array_merge($defaults_month, ["title" => "Dec"]),
        ];


        foreach ($transactions_set as $transaction) {
            $month_id = 1;
            foreach ($lookups as $max_unixtime => $month_num) {
                if ($transaction->getUnixtime() < $max_unixtime) {
                    $month_id = $month_num;
                    break;
                }
            }
            $month_datasets[$month_id]["sum"] += $transaction->getAmount();
            $month_datasets[$month_id]["counted"] += 1;
            if ($transaction->getRenew() == 1) {
                $month_datasets[$month_id]["renew"] += 1;
                $month_datasets[$month_id]["amount_renew"] += $transaction->getAmount();
                $renewed_rentals++;
                $amount_renew += $transaction->getAmount();
            } else {
                $month_datasets[$month_id]["new"] += 1;
                $month_datasets[$month_id]["amount_new"] += $transaction->getAmount();
                $new_rentals++;
                $amount_new += $transaction->getAmount();
            }
        }


        $last_month = array_merge($defaults_month, ["title" => "None"]);
        $best_month = $last_month;

        $best_month_total = 0;
        foreach ($month_datasets as $dataset) {
            $sum = $dataset["amount_new"] + $dataset["amount_renew"];
            if ($sum > $best_month_total) {
                $best_month = $dataset;
                $best_month_total = $sum;
            }
        }
        $table_head = [
            "Month",
            "L$ total",
            "Transactions total",
            "Count / New",
            "Count / Renew",
            "L$ / total new",
            "L$ total renew",
            "Change from last month",
            "Change from best month",
        ];
        $table_body = [];
        foreach ($month_datasets as $dataset) {
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
                    $entry[] = $this->amountChanged($last_month["sum"], $dataset["sum"]);
                }
                if ($dataset["title"] != $best_month["title"]) {
                    $entry[] = $this->amountChanged($best_month["sum"], $dataset["sum"]);
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
        $pages["Fast report"] = $this->renderTable(
            ["New","Renews","L$ total [New]","L$ total [Rewew]"],
            [[$new_rentals,$renewed_rentals,$amount_new,$amount_renew]]
        );
        $pages["Month breakdown"] = $this->renderTable($table_head, $table_body);
        $paged_info = new PagedInfo();
        $this->setSwapTag("page_content", $paged_info->render($pages));
    }
}
