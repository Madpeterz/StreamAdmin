<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\StreamSet as StreamSet;

// Do not edit this file, rerun gen.php to update!
class Server extends genClass
{
    protected $use_table = "server";
    // Data Design
    protected $fields = [
        "id",
        "domain",
        "controlPanelURL",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "domain" => ["type" => "str", "value" => null],
        "controlPanelURL" => ["type" => "str", "value" => null],
    ];
    // Setters
    /**
    * setDomain
    */
    public function setDomain(?string $newValue): UpdateReply
    {
        return $this->updateField("domain", $newValue);
    }
    /**
    * setControlPanelURL
    */
    public function setControlPanelURL(?string $newValue): UpdateReply
    {
        return $this->updateField("controlPanelURL", $newValue);
    }
    // Getters
    public function getDomain(): ?string
    {
        return $this->getField("domain");
    }
    public function getControlPanelURL(): ?string
    {
        return $this->getField("controlPanelURL");
    }
    // Loaders
    public function loadByDomain(string $domain): SingleLoadReply
    {
        return $this->loadByField(
            "domain",
            $domain
        );
    }
    public function loadByControlPanelURL(string $controlPanelURL): SingleLoadReply
    {
        return $this->loadByField(
            "controlPanelURL",
            $controlPanelURL
        );
    }
    public function relatedStream(?array $limitFields = null): StreamSet
    {
        $ids = [$this->getId()];
        $collection = new StreamSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromServerLinks($ids);
        return $collection;
    }
}
// please do not edit this file
