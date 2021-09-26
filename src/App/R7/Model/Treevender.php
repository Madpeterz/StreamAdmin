<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Treevender extends genClass
{
    protected $use_table = "treevender";
    // Data Design
    protected $fields = [
        "id",
        "name",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
    ];
    // Getters
    public function getName(): ?string
    {
        return $this->getField("name");
    }
    // Setters
    /**
    * setName
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setName(?string $newvalue): array
    {
        return $this->updateField("name", $newvalue);
    }
    // Loaders
    public function loadByName(string $name): bool
    {
        return $this->loadByField("name", $name);
    }
}
// please do not edit this file
