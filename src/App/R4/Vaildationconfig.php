<?php

namespace App\R4;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Vaildationconfig extends genClass
{
    protected $use_table = "vaildationconfig";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
        "enabled" => ["type" => "int", "value" => 0],
        "code" => ["type" => "str", "value" => null],
    ];
    public function getName(): ?string
    {
        return $this->getField("name");
    }
    public function getEnabled(): ?int
    {
        return $this->getField("enabled");
    }
    public function getCode(): ?string
    {
        return $this->getField("code");
    }
    /**
    * setName
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setName(?string $newvalue): array
    {
        return $this->updateField("name", $newvalue);
    }
    /**
    * setEnabled
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEnabled(?int $newvalue): array
    {
        return $this->updateField("enabled", $newvalue);
    }
    /**
    * setCode
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setCode(?string $newvalue): array
    {
        return $this->updateField("code", $newvalue);
    }
}
// please do not edit this file