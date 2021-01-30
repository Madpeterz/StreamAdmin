<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Event as Event;

// Do not edit this file, rerun gen.php to update!
class EventSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Event");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Event
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Event
    {
        return parent::getFirst();
    }
}
