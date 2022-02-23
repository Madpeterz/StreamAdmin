<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use App\Models\Sets\PackageSet as PackageSet;

// Do not edit this file, rerun gen.php to update!
class Template extends genClass
{
    protected $use_table = "template";
    // Data Design
    protected $fields = [
        "id",
        "name",
        "detail",
        "notecardDetail",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
        "detail" => ["type" => "str", "value" => null],
        "notecardDetail" => ["type" => "str", "value" => null],
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
    /**
    * setDetail
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setDetail(?string $newvalue): array
    {
        return $this->updateField("detail", $newvalue);
    }
    /**
    * setNotecardDetail
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setNotecardDetail(?string $newvalue): array
    {
        return $this->updateField("notecardDetail", $newvalue);
    }
    // Getters
    public function getName(): ?string
    {
        return $this->getField("name");
    }
    public function getDetail(): ?string
    {
        return $this->getField("detail");
    }
    public function getNotecardDetail(): ?string
    {
        return $this->getField("notecardDetail");
    }
    // Loaders
    public function loadByName(string $name): bool
    {
        return $this->loadByField(
            "name",
            $name
        );
    }
    public function loadByDetail(string $detail): bool
    {
        return $this->loadByField(
            "detail",
            $detail
        );
    }
    public function loadByNotecardDetail(string $notecardDetail): bool
    {
        return $this->loadByField(
            "notecardDetail",
            $notecardDetail
        );
    }
    public function relatedPackage(): PackageSet
    {
        $ids = [$this->getId()];
        $collection = new PackageSet();
        $collection->loadFromTemplateLinks($ids);
        return $collection;
    }
}
// please do not edit this file
