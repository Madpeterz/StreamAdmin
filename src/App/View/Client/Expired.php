<?php

namespace App\View\Client;

abstract class Expired extends Withstatus
{
    public function process()
    {
        $this->whereconfig = [
            "fields" => ["expireunixtime"],
            "values" => [time()],
            "types" => ["i"],
            "matches" => ["<="],
        ];
        $this->output->setSwapTagString("page_actions", "<a href='[[url_base]]client/bulkremove'>"
        . "<button type='button' class='btn btn-outline-danger btn-sm'>Bulk remove</button></a>");
        $this->output->addSwapTagString("page_title", "With status: Expired");
        parent::process();
    }
}
