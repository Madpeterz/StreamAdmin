<?php

namespace App\Endpoint\View\Stream;

use App\R7\Model\Server;

class NeedWork extends Withstatus
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " With status: Need work");
        $this->whereconfig = [
            "fields" => ["needWork"],
            "values" => [1],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process();
        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]stream/bulkupdate'><button type='button' class='btn btn-outline-warning btn-sm'>"
            . "Bulk update</button></a>"
        );
    }
}
