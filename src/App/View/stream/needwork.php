<?php

namespace App\View\Stream;

use App\Server;

class Needwork extends Withstatus
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " With status: Need work");
        $whereconfig = [
            "fields" => ["needwork"],
            "values" => [1],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process();
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]stream/bulkupdate'><button type='button' class='btn btn-outline-warning btn-sm'>"
            . "Bulk update</button></a>"
        );
    }
}
