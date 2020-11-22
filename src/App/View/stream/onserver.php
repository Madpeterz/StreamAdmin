<?php

namespace App\View\Stream;

use App\Server;

class Onserver extends Withstatus
{
    public function process(): void
    {
        $server = new Server();
        $server->loadID($this->page);
        $this->output->setSwapTagString("page_title", " On server: " . $server->getDomain() . "");
        $whereconfig = [
            "fields" => ["serverlink"],
            "values" => [$server->getId()],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process();
    }
}
