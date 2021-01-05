<?php

namespace YAPF\Junk;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Endoftestwithfourentrys extends genClass
{
    protected $use_table = "test.endoftestwithfourentrys";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "value" => ["type" => "str", "value" => null],
    ];
    public function getValue(): ?string
    {
        return $this->getField("value");
    }
    /**
    * setValue
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setValue(?string $newvalue): array
    {
        return $this->updateField("value", $newvalue);
    }
}
// please do not edit this file