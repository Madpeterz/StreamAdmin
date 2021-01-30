<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Template extends genClass
{
    protected $use_table = "template";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
        "detail" => ["type" => "str", "value" => null],
        "notecardDetail" => ["type" => "str", "value" => null],
    ];
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
}
// please do not edit this file
