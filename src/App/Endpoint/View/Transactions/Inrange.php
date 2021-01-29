<?php

namespace App\Endpoint\View\Transactions;

use YAPF\InputFilter\InputFilter;

class Inrange extends RangeForm
{
    public function process(): void
    {
        $input = new InputFilter();
        $this->month = $input->getFilter("month", "integer");
        $this->year = $input->getFilter("year", "integer");

        if ($this->month < 1) {
            $this->month = 1;
        } elseif ($this->month > 12) {
            $this->month = 12;
        }
        if ($this->year < 2013) {
            $this->year = 2013;
        } elseif ($this->year > date("Y")) {
            $this->year = date("Y");
        }

        $start_unixtime = mktime(0, 0, 1, $this->month, 1, $this->year);
        $end_month = $this->month + 1;
        $end_year = $this->year;
        if ($end_month > 12) {
            $end_year += 1;
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
        $this->package_set->loadIds($this->transaction_set->getAllByField("packageLink"));
        $this->region_set->loadIds($this->transaction_set->getAllByField("regionLink"));
        $this->avatar_set->loadIds($this->transaction_set->getAllByField("avatarLink"));
        parent::process();
    }
}
