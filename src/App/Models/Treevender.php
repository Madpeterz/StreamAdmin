<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\TreevenderpackagesSet as TreevenderpackagesSet;

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
    // Setters
    /**
    * setName
    */
    public function setName(?string $newValue): UpdateReply
    {
        return $this->updateField("name", $newValue);
    }
    /**
    * setTextureWaiting
    */
    public function setTextureWaiting(?string $newValue): UpdateReply
    {
        return $this->updateField("textureWaiting", $newValue);
    }
    /**
    * setTextureInuse
    */
    public function setTextureInuse(?string $newValue): UpdateReply
    {
        return $this->updateField("textureInuse", $newValue);
    }
    /**
    * setHideSoldout
    */
    public function setHideSoldout(?bool $newValue): UpdateReply
    {
        return $this->updateField("hideSoldout", $newValue);
    }
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
    // Loaders
    public function loadByName(string $name): SingleLoadReply
    {
        return $this->loadByField(
            "name",
            $name
        );
    }
    public function loadByTextureWaiting(string $textureWaiting): SingleLoadReply
    {
        return $this->loadByField(
            "textureWaiting",
            $textureWaiting
        );
    }
    public function loadByTextureInuse(string $textureInuse): SingleLoadReply
    {
        return $this->loadByField(
            "textureInuse",
            $textureInuse
        );
    }
    public function loadByHideSoldout(bool $hideSoldout): SingleLoadReply
    {
        return $this->loadByField(
            "hideSoldout",
            $hideSoldout
        );
    }
    public function relatedTreevenderpackages(?array $limitFields = null): TreevenderpackagesSet
    {
        $ids = [$this->getId()];
        $collection = new TreevenderpackagesSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromTreevenderLinks($ids);
        return $collection;
    }
}
// please do not edit this file
