<?php

namespace App\Endpoint\View\Reports;

use App\Models\Sets\TransactionsSet;
use YAPF\Bootstrap\Template\PagedInfo;

class BreakdownMonth extends View
{
    public function process(): void
    {
        $year = $this->input->get("year")->asInt();
        if ($year < 2013) {
            $year = 2013;
        } elseif ($year > date("Y")) {
            $year = date("Y");
        }

        $months = [
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "June",
            7 => "July",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec",
        ];
        $month = $this->input->get("month")->asInt();
        if ($month < 1) {
            $month = 1;
        } elseif ($month > 12) {
            $month = 12;
        }

        $this->output->addSwapTagString("page_title", "Month breakdown: " . $months[$month] . " / " . $year);

        $transactions_set = new TransactionsSet();
        $whereconfig = [
        "fields" => ["unixtime","unixtime"],
        "values" => [
            mktime(0, 0, 1, $month, 1, $year),
            mktime(23, 59, 59, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year), $year),
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
        mktime(23, 59, 59, $month, 7, $year) => 1,
        mktime(23, 59, 59, $month, 14, $year) => 2,
        mktime(23, 59, 59, $month, 21, $year) => 3,
        mktime(23, 59, 59, $month, 28, $year) => 4,
        mktime(23, 59, 59, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year), $year) => 5,
        ];

        $default_month = ["new" => 0,"renew" => 0,"amount_new" => 0,"amount_renew" => 0,"sum" => 0,"counted" => 0];
        $month_datasets = [
        1 => array_merge($default_month, ["title" => "Week 1"]),
        2 => array_merge($default_month, ["title" => "Week 2"]),
        3 => array_merge($default_month, ["title" => "Week 3"]),
        4 => array_merge($default_month, ["title" => "Week 4"]),
        5 => array_merge($default_month, ["title" => "Week 5"]),
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
                continue;
            }
            $month_datasets[$month_id]["new"] += 1;
            $month_datasets[$month_id]["amount_new"] += $transaction->getAmount();
            $new_rentals++;
            $amount_new += $transaction->getAmount();
        }


        $last_month = array_merge($default_month, ["title" => "None"]);
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
            "Change from last week",
            "Change from best week",
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
