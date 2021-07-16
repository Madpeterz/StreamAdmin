<?php

namespace App\Endpoint\View\Stream;

use App\R7\Model\Server;

class Onserver extends Withstatus
{
    public function process(): void
    {
        $server = new Server();
        $server->loadID($this->page);
        $this->setSwapTag("page_title", " On server: " . $server->getDomain() . "");
        $this->whereconfig = [
            "fields" => ["serverLink"],
            "values" => [$server->getId()],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process();
    }
}
