<?php

namespace App\Models\Sets;

use YAPF\Framework\Responses\DbObjects\SetsLoadReply as SetsLoadReply;
use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use App\Models\Auditlog as Auditlog;

// Do not edit this file, rerun gen.php to update!
class AuditlogSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Auditlog");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Auditlog
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Auditlog
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldName, $value): ?Auditlog
    {
        return parent::getObjectByField($fieldName, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Auditlog
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
     * uniqueStores
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueStores(): array
    {
        return parent::uniqueArray("store");
    }
    /**
     * uniqueSourceids
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueSourceids(): array
    {
        return parent::uniqueArray("sourceid");
    }
    /**
     * uniqueValuenames
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueValuenames(): array
    {
        return parent::uniqueArray("valuename");
    }
    /**
     * uniqueOldvalues
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueOldvalues(): array
    {
        return parent::uniqueArray("oldvalue");
    }
    /**
     * uniqueNewvalues
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueNewvalues(): array
    {
        return parent::uniqueArray("newvalue");
    }
    /**
     * uniqueUnixtimes
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueUnixtimes(): array
    {
        return parent::uniqueArray("unixtime");
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
    // Loaders
    /**
     * loadById
    */
    public function loadById(
        int $id, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
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
     * loadByStore
    */
    public function loadByStore(
        string $store, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "store", 
            $store, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromStores
    */
    public function loadFromStores(array $values): SetsLoadReply
    {
        return $this->loadIndexes("store", $values);
    }
    /**
     * loadBySourceid
    */
    public function loadBySourceid(
        string $sourceid, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "sourceid", 
            $sourceid, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromSourceids
    */
    public function loadFromSourceids(array $values): SetsLoadReply
    {
        return $this->loadIndexes("sourceid", $values);
    }
    /**
     * loadByValuename
    */
    public function loadByValuename(
        string $valuename, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "valuename", 
            $valuename, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromValuenames
    */
    public function loadFromValuenames(array $values): SetsLoadReply
    {
        return $this->loadIndexes("valuename", $values);
    }
    /**
     * loadByOldvalue
    */
    public function loadByOldvalue(
        string $oldvalue, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "oldvalue", 
            $oldvalue, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromOldvalues
    */
    public function loadFromOldvalues(array $values): SetsLoadReply
    {
        return $this->loadIndexes("oldvalue", $values);
    }
    /**
     * loadByNewvalue
    */
    public function loadByNewvalue(
        string $newvalue, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "newvalue", 
            $newvalue, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromNewvalues
    */
    public function loadFromNewvalues(array $values): SetsLoadReply
    {
        return $this->loadIndexes("newvalue", $values);
    }
    /**
     * loadByUnixtime
    */
    public function loadByUnixtime(
        int $unixtime, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "unixtime", 
            $unixtime, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromUnixtimes
    */
    public function loadFromUnixtimes(array $values): SetsLoadReply
    {
        return $this->loadIndexes("unixtime", $values);
    }
    /**
     * loadByAvatarLink
    */
    public function loadByAvatarLink(
        int $avatarLink, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
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
    // Related loaders
    public function relatedAvatar(): AvatarSet
    {
        $ids = $this->uniqueAvatarLinks();
        $collection = new AvatarSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
}