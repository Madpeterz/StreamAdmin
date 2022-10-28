<?php

namespace App\Endpoint\View\Client;

class Active extends Withstatus
{
    public function process(): void
    {
        global $unixtime_day;
        $this->whereconfig = [
        "fields" => ["expireUnixtime"],
        "values" => [time() + $unixtime_day],
        "types" => ["i"],
        "matches" => [">="],
        ];
        $this->output->addSwapTagString("page_title", "With status: Active");
        parent::process();
    }
}
