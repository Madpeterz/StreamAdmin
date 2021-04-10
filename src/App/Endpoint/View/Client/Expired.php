<?php

namespace App\Endpoint\View\Client;

class Expired extends Withstatus
{
    public function process(): void
    {
        $this->whereconfig = [
            "fields" => ["expireUnixtime"],
            "values" => [time()],
            "types" => ["i"],
            "matches" => ["<="],
        ];
        $this->setSwapTag("page_actions", "<a href='[[url_base]]client/BulkRemove'>"
        . "<button type='button' class='btn btn-outline-danger btn-sm'>Bulk remove</button></a>");
        $this->output->addSwapTagString("page_title", "With status: Expired");
        parent::process();
    }
}
