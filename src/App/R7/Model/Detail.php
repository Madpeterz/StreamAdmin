<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Detail extends genClass
{
    protected $use_table = "detail";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "rentalLink" => ["type" => "int", "value" => null],
    ];
    public function getRentalLink(): ?int
    {
        return $this->getField("rentalLink");
    }
    /**
    * setRentalLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setRentalLink(?int $newvalue): array
    {
        return $this->updateField("rentalLink", $newvalue);
    }
}
// please do not edit this file
