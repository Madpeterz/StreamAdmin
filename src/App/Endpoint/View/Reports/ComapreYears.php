<?php

namespace App\Endpoint\View\Reports;

use App\Models\TransactionsSet;
use YAPF\InputFilter\InputFilter;

class ComapreYears extends View
{
    public function process(): void
    {
        $input = new InputFilter();
        $yeara = $input->getFilter("yeara", "integer");
        if ($yeara < 2013) {
            $yeara = 2013;
        } elseif ($yeara > date("Y")) {
            $yeara = date("Y");
        }

        $yearb = $input->getFilter("yearb", "integer");
        if ($yearb < 2013) {
            $yearb = 2013;
        } elseif ($yearb > date("Y")) {
            $yearb = date("Y");
        }

        if ($yearb < $yeara) {
            $swap = $yeara;
            $yeara = $yearb;
            $yearb = $swap;
        }
        $this->output->addSwapTagString("page_title", "Year vs Year: " . $yeara . " vs " . $yearb);

        $transactions_set_a = new TransactionsSet();
        $values = [
            mktime(0, 0, 1, 1, 1, $yeara),
            mktime(23, 59, 59, 12, cal_days_in_month(CAL_GREGORIAN, 12, $yeara), $yeara),
        ];
        $whereconfig = [
        "fields" => ["unixtime","unixtime"],
        "values" => $values,
        "types" => ["i","i"],
        "matches" => [">=","<="],
        ];
        $transactions_set_a->loadWithConfig($whereconfig);

        $transactions_set_b = new TransactionsSet();
        $values = [
            mktime(0, 0, 1, 1, 1, $yearb),
            mktime(23, 59, 59, 12, cal_days_in_month(CAL_GREGORIAN, 12, $yearb), $yearb),
        ];
        $whereconfig = [
        "fields" => ["unixtime","unixtime"],
        "values" => $values,
        "types" => ["i","i"],
        "matches" => [">=","<="],
        ];
        $transactions_set_b->loadWithConfig($whereconfig);

        $lookups_yeara = [
        mktime(23, 59, 59, 1, cal_days_in_month(CAL_GREGORIAN, 1, $yeara), $yeara) => 1,
        mktime(23, 59, 59, 2, cal_days_in_month(CAL_GREGORIAN, 2, $yeara), $yeara) => 2,
        mktime(23, 59, 59, 3, cal_days_in_month(CAL_GREGORIAN, 3, $yeara), $yeara) => 3,
        mktime(23, 59, 59, 4, cal_days_in_month(CAL_GREGORIAN, 4, $yeara), $yeara) => 4,
        mktime(23, 59, 59, 5, cal_days_in_month(CAL_GREGORIAN, 5, $yeara), $yeara) => 5,
        mktime(23, 59, 59, 6, cal_days_in_month(CAL_GREGORIAN, 6, $yeara), $yeara) => 6,
        mktime(23, 59, 59, 7, cal_days_in_month(CAL_GREGORIAN, 7, $yeara), $yeara) => 7,
        mktime(23, 59, 59, 8, cal_days_in_month(CAL_GREGORIAN, 8, $yeara), $yeara) => 8,
        mktime(23, 59, 59, 9, cal_days_in_month(CAL_GREGORIAN, 9, $yeara), $yeara) => 9,
        mktime(23, 59, 59, 10, cal_days_in_month(CAL_GREGORIAN, 10, $yeara), $yeara) => 10,
        mktime(23, 59, 59, 11, cal_days_in_month(CAL_GREGORIAN, 11, $yeara), $yeara) => 11,
        mktime(23, 59, 59, 12, cal_days_in_month(CAL_GREGORIAN, 12, $yeara), $yeara) => 12,
        ];
        $lookups_yearb = [
        mktime(23, 59, 59, 1, cal_days_in_month(CAL_GREGORIAN, 1, $yearb), $yearb) => 1,
        mktime(23, 59, 59, 2, cal_days_in_month(CAL_GREGORIAN, 2, $yearb), $yearb) => 2,
        mktime(23, 59, 59, 3, cal_days_in_month(CAL_GREGORIAN, 3, $yearb), $yearb) => 3,
        mktime(23, 59, 59, 4, cal_days_in_month(CAL_GREGORIAN, 4, $yearb), $yearb) => 4,
        mktime(23, 59, 59, 5, cal_days_in_month(CAL_GREGORIAN, 5, $yearb), $yearb) => 5,
        mktime(23, 59, 59, 6, cal_days_in_month(CAL_GREGORIAN, 6, $yearb), $yearb) => 6,
        mktime(23, 59, 59, 7, cal_days_in_month(CAL_GREGORIAN, 7, $yearb), $yearb) => 7,
        mktime(23, 59, 59, 8, cal_days_in_month(CAL_GREGORIAN, 8, $yearb), $yearb) => 8,
        mktime(23, 59, 59, 9, cal_days_in_month(CAL_GREGORIAN, 9, $yearb), $yearb) => 9,
        mktime(23, 59, 59, 10, cal_days_in_month(CAL_GREGORIAN, 10, $yearb), $yearb) => 10,
        mktime(23, 59, 59, 11, cal_days_in_month(CAL_GREGORIAN, 11, $yearb), $yearb) => 11,
        mktime(23, 59, 59, 12, cal_days_in_month(CAL_GREGORIAN, 12, $yearb), $yearb) => 12,
        ];

        $yeara_month_datasets = [
        1 => ["title" => "Jan","sum" => 0,"counted" => 0],
        2 => ["title" => "Feb","sum" => 0,"counted" => 0],
        3 => ["title" => "Mar","sum" => 0,"counted" => 0],
        4 => ["title" => "Apr","sum" => 0,"counted" => 0],
        5 => ["title" => "May","sum" => 0,"counted" => 0],
        6 => ["title" => "June","sum" => 0,"counted" => 0],
        7 => ["title" => "July","sum" => 0,"counted" => 0],
        8 => ["title" => "Aug","sum" => 0,"counted" => 0],
        9 => ["title" => "Sep","sum" => 0,"counted" => 0],
        10 => ["title" => "Oct","sum" => 0,"counted" => 0],
        11 => ["title" => "Nov","sum" => 0,"counted" => 0],
        12 => ["title" => "Dec","sum" => 0,"counted" => 0],
        ];
        $yearb_month_datasets = [
        1 => ["title" => "Jan","sum" => 0,"counted" => 0],
        2 => ["title" => "Feb","sum" => 0,"counted" => 0],
        3 => ["title" => "Mar","sum" => 0,"counted" => 0],
        4 => ["title" => "Apr","sum" => 0,"counted" => 0],
        5 => ["title" => "May","sum" => 0,"counted" => 0],
        6 => ["title" => "June","sum" => 0,"counted" => 0],
        7 => ["title" => "July","sum" => 0,"counted" => 0],
        8 => ["title" => "Aug","sum" => 0,"counted" => 0],
        9 => ["title" => "Sep","sum" => 0,"counted" => 0],
        10 => ["title" => "Oct","sum" => 0,"counted" => 0],
        11 => ["title" => "Nov","sum" => 0,"counted" => 0],
        12 => ["title" => "Dec","sum" => 0,"counted" => 0],
        ];

        $year_a_total = 0;
        $year_a_count = 0;
        $year_b_total = 0;
        $year_b_count = 0;
        foreach ($transactions_set_a->getAllIds() as $transaction_id) {
            $transaction = $transactions_set_a->getObjectByID($transaction_id);
            $month_id = 1;
            foreach ($lookups_yeara as $max_unixtime => $month_num) {
                if ($transaction->getUnixtime() < $max_unixtime) {
                    $month_id = $month_num;
                    break;
                }
            }
            $yeara_month_datasets[$month_id]["sum"] += $transaction->getAmount();
            $yeara_month_datasets[$month_id]["counted"] += 1;
            $year_a_total += $transaction->getAmount();
            $year_a_count += 1;
        }
        $yeara_month_datasets[13] = ["title" => "Total","sum" => $year_a_total,"counted" => $year_a_count];

        foreach ($transactions_set_b->getAllIds() as $transaction_id) {
            $transaction = $transactions_set_b->getObjectByID($transaction_id);
            $month_id = 1;
            foreach ($lookups_yearb as $max_unixtime => $month_num) {
                if ($transaction->getUnixtime() < $max_unixtime) {
                    $month_id = $month_num;
                    break;
                }
            }
            $yearb_month_datasets[$month_id]["sum"] += $transaction->getAmount();
            $yearb_month_datasets[$month_id]["counted"] += 1;
            $year_b_total += $transaction->getAmount();
            $year_b_count += 1;
        }
        $yearb_month_datasets[13] = ["title" => "Total","sum" => $year_b_total,"counted" => $year_b_count];

        $table_head = [
            $yeara,
            "L$ total",
            "Transactions",
            $yearb,
            "Transactions",
            "L$ total",
            "Change L$",
            "Change Transactions",
        ];

        $table_body = [];
        foreach ($yeara_month_datasets as $index => $dataset) {
            $entry = [];
            $dataset2 = $yearb_month_datasets[$index];
            // "title"=>"Dec","new"=>0,"renew"=>0,"amount_new"=>0,"amount_renew"=>0,"sum"=>0,"counted"=>0
            $entry[] = $dataset["title"];
            if ($dataset["sum"] > 0) {
                $entry[] = $dataset["sum"];
                $entry[] = $dataset["counted"];
            } else {
                $entry[] = "";
                $entry[] = "";
            }
            $entry[] = $dataset["title"];
            if ($dataset2["sum"] > 0) {
                $entry[] = $dataset2["sum"];
                $entry[] = $dataset2["counted"];
            } else {
                $entry[] = "";
                $entry[] = "";
            }
            $entry[] = $this->amountChanged($dataset["sum"], $dataset2["sum"]);
            $entry[] = $this->amountChanged($dataset["counted"], $dataset2["counted"]);
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", render_table($table_head, $table_body));
    }
}
