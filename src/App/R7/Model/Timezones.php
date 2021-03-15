<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Timezones extends genClass
{
    protected $use_table = "timezones";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
        "code" => ["type" => "str", "value" => null],
    ];
    public function getName(): ?string
    {
        return $this->getField("name");
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
    * setCode
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setCode(?string $newvalue): array
    {
        return $this->updateField("code", $newvalue);
    }
}
// please do not edit this file