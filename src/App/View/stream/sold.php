<?php

namespace App\View\Stream;

use App\Server;

class Sold extends Withstatus
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " With status: Sold");
        $whereconfig = [
            "fields" => ["rentallink"],
            "values" => [null],
            "types" => ["s"],
            "matches" => ["IS NOT"],
        ];
        parent::process();
    }
}
