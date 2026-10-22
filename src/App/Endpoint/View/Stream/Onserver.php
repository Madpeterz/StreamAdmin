<?php

namespace App\Endpoint\View\Stream;

use App\Models\Server;

class Onserver extends Withstatus
{
    public function process(bool $usePackageNotServer = true): void
    {
        $server = new Server();
        $server->loadID($this->siteConfig->getPage());
        $this->setSwapTag("page_title", " On server: " . $server->_Domain . "");
        $this->whereconfig = [
            "fields" => ["serverLink"],
            "values" => [$server->_Id],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process($usePackageNotServer);
    }
}
