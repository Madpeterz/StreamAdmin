<?php

namespace App\Endpoint\View\Stream;

use App\Models\Server;

class Sold extends Withstatus
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " With status: Sold");
        $whereconfig = [
            "fields" => ["rentalLink"],
            "values" => [null],
            "types" => ["s"],
            "matches" => ["IS NOT"],
        ];
        parent::process();
    }
}
