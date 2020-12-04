<?php

namespace App\View\Transactions;

use YAPF\InputFilter\InputFilter;

class Inrange extends RangeForm
{
    public function process(): void
    {
        $input = new InputFilter();
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

        $this->transaction_set->loadWithConfig($whereconfig);
        $this->package_set->loadIds($this->transaction_set->getAllByField("packagelink"));
        $this->region_set->loadIds($this->transaction_set->getAllByField("regionlink"));
        $this->avatar_set->loadIds($this->transaction_set->getAllByField("avatarlink"));
        parent::process();
    }
}
