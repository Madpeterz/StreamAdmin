<?php

namespace App\Endpoints\View\Stream;

use App\Models\Server;

class Onserver extends Withstatus
{
    public function process(): void
    {
        $server = new Server();
        $server->loadID($this->page);
        $this->setSwapTag("page_title", " On server: " . $server->getDomain() . "");
        $whereconfig = [
            "fields" => ["serverlink"],
            "values" => [$server->getId()],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process();
    }
}
