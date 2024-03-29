<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Detail as Detail;

// Do not edit this file, rerun gen.php to update!
class DetailSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Detail");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Detail
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Detail
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Detail
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Detail
    {
        return parent::current();
    }
    // Loaders
    /**
     * loadByRentalLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByRentalLink(
                    int $rentalLink, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("rentalLink", $rentalLink, $limit, $orderBy, $orderDir);
    }
}
