<?php

namespace App\View\Client;

abstract class Active extends Withstatus
{
    public function process()
    {
        global $unixtime_day;
        $this->whereconfig = [
        "fields" => ["expireunixtime"],
        "values" => [time() + $unixtime_day],
        "types" => ["i"],
        "matches" => [">="],
        ];
        $this->output->addSwapTagString("page_title", "With status: Active");
        parent::process();
    }
}
