<?php

namespace App\Endpoint\View\Stream;

class Ready extends Withstatus
{
    public function process(bool $usePackageNotServer = false): void
    {
        $this->setSwapTag("page_title", " With status: Ready");
        $this->whereconfig = [
            "fields" => ["rentalLink","needWork"],
            "values" => [null,0],
            "types" => ["s","i"],
            "matches" => ["IS","="],
        ];
        parent::process(false);
    }
}
