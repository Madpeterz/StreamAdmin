<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\SlconfigSet as SlconfigSet;

// Do not edit this file, rerun gen.php to update!
class Timezones extends genClass
{
    protected $use_table = "timezones";
    // Data Design
    protected $fields = [
        "id",
        "name",
        "code",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "name" => ["type" => "str", "value" => null],
        "code" => ["type" => "str", "value" => null],
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
    * setCode
    */
    public function setCode(?string $newValue): UpdateReply
    {
        return $this->updateField("code", $newValue);
    }
    // Getters
    public function getName(): ?string
    {
        return $this->getField("name");
    }
    public function getCode(): ?string
    {
        return $this->getField("code");
    }
    // Loaders
    public function loadByName(string $name): SingleLoadReply
    {
        return $this->loadByField(
            "name",
            $name
        );
    }
    public function loadByCode(string $code): SingleLoadReply
    {
        return $this->loadByField(
            "code",
            $code
        );
    }
    public function relatedSlconfig(?array $limitFields = null): SlconfigSet
    {
        $ids = [$this->getId()];
        $collection = new SlconfigSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromDisplayTimezoneLinks($ids);
        return $collection;
    }
}
// please do not edit this file
