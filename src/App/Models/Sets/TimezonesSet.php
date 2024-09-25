<?php

namespace App\Models\Sets;

use YAPF\Framework\Responses\DbObjects\SetsLoadReply as SetsLoadReply;
use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use App\Models\Timezones as Timezones;

// Do not edit this file, rerun gen.php to update!
class TimezonesSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Timezones");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Timezones
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Timezones
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldName, $value): ?Timezones
    {
        return parent::getObjectByField($fieldName, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Timezones
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
     * uniqueNames
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueNames(): array
    {
        return parent::uniqueArray("name");
    }
    /**
     * uniqueCodes
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueCodes(): array
    {
        return parent::uniqueArray("code");
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
     * loadByName
    */
    public function loadByName(
        string $name,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "name",
            $name,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromNames
    */
    public function loadFromNames(array $values): SetsLoadReply
    {
        return $this->loadIndexes("name", $values);
    }
    /**
     * loadByCode
    */
    public function loadByCode(
        string $code,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "code",
            $code,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromCodes
    */
    public function loadFromCodes(array $values): SetsLoadReply
    {
        return $this->loadIndexes("code", $values);
    }
    // Related loaders
    public function relatedSlconfig(?array $limitFields=null): SlconfigSet
    {
        $ids = $this->uniqueIds();
        $collection = new SlconfigSet();
        if($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromDisplayTimezoneLinks($ids);
        return $collection;
    }
}
