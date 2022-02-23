<?php

namespace App\Models\Sets;

use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\Models\Servertypes as Servertypes;

// Do not edit this file, rerun gen.php to update!
class ServertypesSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Servertypes");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Servertypes
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Servertypes
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Servertypes
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Servertypes
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
    // Loaders
    /**
     * loadById
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadById(
        int $id, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "id", 
            $id, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromIds
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromIds(array $values): array
    {
        return $this->loadIndexs("id", $values);
    }
    /**
     * loadByName
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByName(
        string $name, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "name", 
            $name, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromNames
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromNames(array $values): array
    {
        return $this->loadIndexs("name", $values);
    }
    // Related loaders
    public function relatedPackage(): PackageSet
    {
        $ids = $this->uniqueIds();
        $collection = new PackageSet();
        $collection->loadFromServertypeLinks($ids);
        return $collection;
    }
}