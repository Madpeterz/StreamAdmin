<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Noticenotecard as Noticenotecard;

// Do not edit this file, rerun gen.php to update!
class NoticenotecardSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Noticenotecard");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Noticenotecard
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Noticenotecard
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Noticenotecard
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Noticenotecard
    {
        return parent::current();
    }
    // Loaders
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByName(string $name, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("name", $name, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByMissing(bool $missing, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("missing", $missing, $limit, $orderBy, $orderDir);
    }
}
