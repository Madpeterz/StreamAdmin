<?php

namespace App\Endpoint\View\Client;

class Active extends Withstatus
{
    public function process(): void
    {
        $this->whereconfig = [
        "fields" => ["expireUnixtime"],
        "values" => [time() + $this->siteConfig->unixtimeDay()],
        "types" => ["i"],
        "matches" => [">="],
        ];
        $this->output->addSwapTagString("page_title", "With status: Active");
        parent::process();
    }
}
