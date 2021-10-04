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
        "textureWaiting",
        "textureInuse",
        "hideSoldout",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
        "textureWaiting" => ["type" => "str", "value" => "00000000-0000-0000-0000-000000000000"],
        "textureInuse" => ["type" => "str", "value" => "00000000-0000-0000-0000-000000000000"],
        "hideSoldout" => ["type" => "bool", "value" => 0],
    ];
    // Getters
    public function getName(): ?string
    {
        return $this->getField("name");
    }
    public function getTextureWaiting(): ?string
    {
        return $this->getField("textureWaiting");
    }
    public function getTextureInuse(): ?string
    {
        return $this->getField("textureInuse");
    }
    public function getHideSoldout(): ?bool
    {
        return $this->getField("hideSoldout");
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
    /**
    * setTextureWaiting
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTextureWaiting(?string $newvalue): array
    {
        return $this->updateField("textureWaiting", $newvalue);
    }
    /**
    * setTextureInuse
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTextureInuse(?string $newvalue): array
    {
        return $this->updateField("textureInuse", $newvalue);
    }
    /**
    * setHideSoldout
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setHideSoldout(?bool $newvalue): array
    {
        return $this->updateField("hideSoldout", $newvalue);
    }
    // Loaders
    public function loadByName(string $name): bool
    {
        return $this->loadByField("name", $name);
    }
    public function loadByTextureWaiting(string $textureWaiting): bool
    {
        return $this->loadByField("textureWaiting", $textureWaiting);
    }
    public function loadByTextureInuse(string $textureInuse): bool
    {
        return $this->loadByField("textureInuse", $textureInuse);
    }
    public function loadByHideSoldout(bool $hideSoldout): bool
    {
        return $this->loadByField("hideSoldout", $hideSoldout);
    }
}
// please do not edit this file
