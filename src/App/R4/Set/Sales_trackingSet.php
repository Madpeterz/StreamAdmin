<?php

namespace App\R4\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R4\Model\Sales_tracking as Sales_tracking;

// Do not edit this file, rerun gen.php to update!
class Sales_trackingSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R4\Model\Sales_tracking");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Sales_tracking
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Sales_tracking
    {
        return parent::getFirst();
    }
}
