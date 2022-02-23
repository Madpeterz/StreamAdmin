<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use App\Models\Sets\PackageSet as PackageSet;

// Do not edit this file, rerun gen.php to update!
class Servertypes extends genClass
{
    protected $use_table = "servertypes";
    // Data Design
    protected $fields = [
        "id",
        "name",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
    ];
    // Setters
    /**
    * setName
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setName(?string $newvalue): array
    {
        return $this->updateField("name", $newvalue);
    }
    // Getters
    public function getName(): ?string
    {
        return $this->getField("name");
    }
    // Loaders
    public function loadByName(string $name): bool
    {
        return $this->loadByField(
            "name",
            $name
        );
    }
    public function relatedPackage(): PackageSet
    {
        $ids = [$this->getId()];
        $collection = new PackageSet();
        $collection->loadFromServertypeLinks($ids);
        return $collection;
    }
}
// please do not edit this file
