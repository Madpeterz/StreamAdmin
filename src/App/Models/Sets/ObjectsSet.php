<?php

namespace App\Models\Sets;

use YAPF\Framework\Responses\DbObjects\SetsLoadReply as SetsLoadReply;
use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use App\Models\Objects as Objects;

// Do not edit this file, rerun gen.php to update!
class ObjectsSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Objects");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Objects
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Objects
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldName, $value): ?Objects
    {
        return parent::getObjectByField($fieldName, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Objects
    {
        return parent::current();
    }
    /**
     * uniqueIds
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueIds(): array
    {
        return parent::uniqueArray("id");
    }
    /**
     * uniqueAvatarLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueAvatarLinks(): array
    {
        return parent::uniqueArray("avatarLink");
    }
    /**
     * uniqueRegionLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueRegionLinks(): array
    {
        return parent::uniqueArray("regionLink");
    }
    /**
     * uniqueObjectUUIDs
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueObjectUUIDs(): array
    {
        return parent::uniqueArray("objectUUID");
    }
    /**
     * uniqueObjectNames
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueObjectNames(): array
    {
        return parent::uniqueArray("objectName");
    }
    /**
     * uniqueObjectModes
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueObjectModes(): array
    {
        return parent::uniqueArray("objectMode");
    }
    /**
     * uniqueObjectXYZs
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueObjectXYZs(): array
    {
        return parent::uniqueArray("objectXYZ");
    }
    /**
     * uniqueLastSeens
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueLastSeens(): array
    {
        return parent::uniqueArray("lastSeen");
    }
    // Loaders
    /**
     * loadById
    */
    public function loadById(
        int $id,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "id",
            $id,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromIds
    */
    public function loadFromIds(array $values): SetsLoadReply
    {
        return $this->loadIndexes("id", $values);
    }
    /**
     * loadByAvatarLink
    */
    public function loadByAvatarLink(
        int $avatarLink,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "avatarLink",
            $avatarLink,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromAvatarLinks
    */
    public function loadFromAvatarLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("avatarLink", $values);
    }
    /**
     * loadByRegionLink
    */
    public function loadByRegionLink(
        int $regionLink,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "regionLink",
            $regionLink,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromRegionLinks
    */
    public function loadFromRegionLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("regionLink", $values);
    }
    /**
     * loadByObjectUUID
    */
    public function loadByObjectUUID(
        string $objectUUID,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "objectUUID",
            $objectUUID,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromObjectUUIDs
    */
    public function loadFromObjectUUIDs(array $values): SetsLoadReply
    {
        return $this->loadIndexes("objectUUID", $values);
    }
    /**
     * loadByObjectName
    */
    public function loadByObjectName(
        string $objectName,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "objectName",
            $objectName,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromObjectNames
    */
    public function loadFromObjectNames(array $values): SetsLoadReply
    {
        return $this->loadIndexes("objectName", $values);
    }
    /**
     * loadByObjectMode
    */
    public function loadByObjectMode(
        string $objectMode,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "objectMode",
            $objectMode,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromObjectModes
    */
    public function loadFromObjectModes(array $values): SetsLoadReply
    {
        return $this->loadIndexes("objectMode", $values);
    }
    /**
     * loadByObjectXYZ
    */
    public function loadByObjectXYZ(
        string $objectXYZ,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "objectXYZ",
            $objectXYZ,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromObjectXYZs
    */
    public function loadFromObjectXYZs(array $values): SetsLoadReply
    {
        return $this->loadIndexes("objectXYZ", $values);
    }
    /**
     * loadByLastSeen
    */
    public function loadByLastSeen(
        int $lastSeen,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "lastSeen",
            $lastSeen,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromLastSeens
    */
    public function loadFromLastSeens(array $values): SetsLoadReply
    {
        return $this->loadIndexes("lastSeen", $values);
    }
    // Related loaders
    public function relatedAvatar(?array $limitFields=null): AvatarSet
    {
        $ids = $this->uniqueAvatarLinks();
        $collection = new AvatarSet();
        if($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedRegion(?array $limitFields=null): RegionSet
    {
        $ids = $this->uniqueRegionLinks();
        $collection = new RegionSet();
        if($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
}
