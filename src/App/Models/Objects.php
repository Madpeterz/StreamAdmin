<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\AvatarSet as AvatarSet;
use App\Models\Sets\RegionSet as RegionSet;

// Do not edit this file, rerun gen.php to update!
class Objects extends genClass
{
    protected $use_table = "objects";
    // Data Design
    protected $fields = [
        "id",
        "avatarLink",
        "regionLink",
        "objectUUID",
        "objectName",
        "objectMode",
        "objectXYZ",
        "lastSeen",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "avatarLink" => ["type" => "int", "value" => null],
        "regionLink" => ["type" => "int", "value" => null],
        "objectUUID" => ["type" => "str", "value" => null],
        "objectName" => ["type" => "str", "value" => null],
        "objectMode" => ["type" => "str", "value" => null],
        "objectXYZ" => ["type" => "str", "value" => null],
        "lastSeen" => ["type" => "int", "value" => 0],
    ];
    // Setters
    /**
    * setAvatarLink
    */
    public function setAvatarLink(?int $newValue): UpdateReply
    {
        return $this->updateField("avatarLink", $newValue);
    }
    /**
    * setRegionLink
    */
    public function setRegionLink(?int $newValue): UpdateReply
    {
        return $this->updateField("regionLink", $newValue);
    }
    /**
    * setObjectUUID
    */
    public function setObjectUUID(?string $newValue): UpdateReply
    {
        return $this->updateField("objectUUID", $newValue);
    }
    /**
    * setObjectName
    */
    public function setObjectName(?string $newValue): UpdateReply
    {
        return $this->updateField("objectName", $newValue);
    }
    /**
    * setObjectMode
    */
    public function setObjectMode(?string $newValue): UpdateReply
    {
        return $this->updateField("objectMode", $newValue);
    }
    /**
    * setObjectXYZ
    */
    public function setObjectXYZ(?string $newValue): UpdateReply
    {
        return $this->updateField("objectXYZ", $newValue);
    }
    /**
    * setLastSeen
    */
    public function setLastSeen(?int $newValue): UpdateReply
    {
        return $this->updateField("lastSeen", $newValue);
    }
    // Getters
    public function getAvatarLink(): ?int
    {
        return $this->getField("avatarLink");
    }
    public function getRegionLink(): ?int
    {
        return $this->getField("regionLink");
    }
    public function getObjectUUID(): ?string
    {
        return $this->getField("objectUUID");
    }
    public function getObjectName(): ?string
    {
        return $this->getField("objectName");
    }
    public function getObjectMode(): ?string
    {
        return $this->getField("objectMode");
    }
    public function getObjectXYZ(): ?string
    {
        return $this->getField("objectXYZ");
    }
    public function getLastSeen(): ?int
    {
        return $this->getField("lastSeen");
    }
    // Loaders
    public function loadByAvatarLink(int $avatarLink): SingleLoadReply
    {
        return $this->loadByField(
            "avatarLink",
            $avatarLink
        );
    }
    public function loadByRegionLink(int $regionLink): SingleLoadReply
    {
        return $this->loadByField(
            "regionLink",
            $regionLink
        );
    }
    public function loadByObjectUUID(string $objectUUID): SingleLoadReply
    {
        return $this->loadByField(
            "objectUUID",
            $objectUUID
        );
    }
    public function loadByObjectName(string $objectName): SingleLoadReply
    {
        return $this->loadByField(
            "objectName",
            $objectName
        );
    }
    public function loadByObjectMode(string $objectMode): SingleLoadReply
    {
        return $this->loadByField(
            "objectMode",
            $objectMode
        );
    }
    public function loadByObjectXYZ(string $objectXYZ): SingleLoadReply
    {
        return $this->loadByField(
            "objectXYZ",
            $objectXYZ
        );
    }
    public function loadByLastSeen(int $lastSeen): SingleLoadReply
    {
        return $this->loadByField(
            "lastSeen",
            $lastSeen
        );
    }
    public function relatedAvatar(): AvatarSet
    {
        $ids = [$this->getAvatarLink()];
        $collection = new AvatarSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedRegion(): RegionSet
    {
        $ids = [$this->getRegionLink()];
        $collection = new RegionSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
}
// please do not edit this file
