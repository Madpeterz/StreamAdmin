<?php

namespace App\Endpoints\View\Client;

class Soon extends Withstatus
{
    public function process(): void
    {
        global $unixtime_day;
        $this->whereconfig = [
        "fields" => ["expireunixtime","expireunixtime"],
        "values" => [time() + $unixtime_day,time()],
        "types" => ["i","i"],
        "matches" => ["<=",">"],
        ];
        $this->output->addSwapTagString("page_title", "With status: Soon");
        parent::process();
    }
}
