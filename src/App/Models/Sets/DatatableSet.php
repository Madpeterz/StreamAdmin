<?php

namespace App\Models\Sets;

use YAPF\Framework\Responses\DbObjects\SetsLoadReply as SetsLoadReply;
use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use App\Models\Datatable as Datatable;

// Do not edit this file, rerun gen.php to update!
class DatatableSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Datatable");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Datatable
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Datatable
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldName, $value): ?Datatable
    {
        return parent::getObjectByField($fieldName, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Datatable
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
     * uniqueHideColZeros
     * returns unique values from the collection matching that field
     * @return array<bool>
     */
    public function uniqueHideColZeros(): array
    {
        return parent::uniqueArray("hideColZero");
    }
    /**
     * uniqueCols
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueCols(): array
    {
        return parent::uniqueArray("col");
    }
    /**
     * uniqueColss
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueColss(): array
    {
        return parent::uniqueArray("cols");
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
     * uniqueDirs
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueDirs(): array
    {
        return parent::uniqueArray("dir");
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
     * loadByHideColZero
    */
    public function loadByHideColZero(
        bool $hideColZero, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "hideColZero", 
            $hideColZero, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromHideColZeros
    */
    public function loadFromHideColZeros(array $values): SetsLoadReply
    {
        return $this->loadIndexes("hideColZero", $values);
    }
    /**
     * loadByCol
    */
    public function loadByCol(
        int $col, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "col", 
            $col, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromCols
    */
    public function loadFromCols(array $values): SetsLoadReply
    {
        return $this->loadIndexes("col", $values);
    }
    /**
     * loadByCols
    */
    public function loadByCols(
        string $cols, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "cols", 
            $cols, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromColss
    */
    public function loadFromColss(array $values): SetsLoadReply
    {
        return $this->loadIndexes("cols", $values);
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
     * loadByDir
    */
    public function loadByDir(
        string $dir, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "dir", 
            $dir, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromDirs
    */
    public function loadFromDirs(array $values): SetsLoadReply
    {
        return $this->loadIndexes("dir", $values);
    }
    // Related loaders
}
