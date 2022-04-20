<?php

namespace App\Models\Sets;

use YAPF\Framework\Responses\DbObjects\SetsLoadReply as SetsLoadReply;
use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use App\Models\Template as Template;

// Do not edit this file, rerun gen.php to update!
class TemplateSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Template");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Template
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Template
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldName, $value): ?Template
    {
        return parent::getObjectByField($fieldName, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Template
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
     * uniqueDetails
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueDetails(): array
    {
        return parent::uniqueArray("detail");
    }
    /**
     * uniqueNotecardDetails
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueNotecardDetails(): array
    {
        return parent::uniqueArray("notecardDetail");
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
     * loadByName
    */
    public function loadByName(
        string $name, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
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
     * loadByDetail
    */
    public function loadByDetail(
        string $detail, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "detail", 
            $detail, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromDetails
    */
    public function loadFromDetails(array $values): SetsLoadReply
    {
        return $this->loadIndexes("detail", $values);
    }
    /**
     * loadByNotecardDetail
    */
    public function loadByNotecardDetail(
        string $notecardDetail, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "notecardDetail", 
            $notecardDetail, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromNotecardDetails
    */
    public function loadFromNotecardDetails(array $values): SetsLoadReply
    {
        return $this->loadIndexes("notecardDetail", $values);
    }
    // Related loaders
    public function relatedPackage(): PackageSet
    {
        $ids = $this->uniqueIds();
        $collection = new PackageSet();
        $collection->loadFromTemplateLinks($ids);
        return $collection;
    }
}
