<?php

namespace App\Endpoints\View\Stream;

class Ready extends Withstatus
{
    public function process(): void
    {
        $this->setSwapTag("page_title", " With status: Ready");
        $whereconfig = [
            "fields" => ["rentallink","needwork"],
            "values" => [null,0],
            "types" => ["s","i"],
            "matches" => ["IS","="],
        ];
        parent::process();
    }
}
