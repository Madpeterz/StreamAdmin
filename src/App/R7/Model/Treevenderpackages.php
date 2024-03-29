<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Treevenderpackages extends genClass
{
    protected $use_table = "treevenderpackages";
    // Data Design
    protected $fields = [
        "id",
        "treevenderLink",
        "packageLink",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "treevenderLink" => ["type" => "int", "value" => null],
        "packageLink" => ["type" => "int", "value" => null],
    ];
    // Getters
    public function getTreevenderLink(): ?int
    {
        return $this->getField("treevenderLink");
    }
    public function getPackageLink(): ?int
    {
        return $this->getField("packageLink");
    }
    // Setters
    /**
    * setTreevenderLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTreevenderLink(?int $newvalue): array
    {
        return $this->updateField("treevenderLink", $newvalue);
    }
    /**
    * setPackageLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPackageLink(?int $newvalue): array
    {
        return $this->updateField("packageLink", $newvalue);
    }
    // Loaders
    public function loadByTreevenderLink(int $treevenderLink): bool
    {
        return $this->loadByField("treevenderLink", $treevenderLink);
    }
    public function loadByPackageLink(int $packageLink): bool
    {
        return $this->loadByField("packageLink", $packageLink);
    }
}
// please do not edit this file
