<?php

namespace App\Endpoint\View\Stream;

use App\Models\Server;

class Onserver extends Withstatus
{
    public function process(): void
    {
        $server = new Server();
        $server->loadID($this->page);
        $this->setSwapTag("page_title", " On server: " . $server->getDomain() . "");
        $whereconfig = [
            "fields" => ["serverLink"],
            "values" => [$server->getId()],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process();
    }
}
