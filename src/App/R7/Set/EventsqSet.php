<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Eventsq as Eventsq;

// Do not edit this file, rerun gen.php to update!
class EventsqSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Eventsq");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Eventsq
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Eventsq
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Eventsq
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Eventsq
    {
        return parent::current();
    }
    // Loaders
    /**
     * loadByEventName
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByEventName(
                    string $eventName, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("eventName", $eventName, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByEventMessage
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByEventMessage(
                    string $eventMessage, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("eventMessage", $eventMessage, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByEventUnixtime
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByEventUnixtime(
                    int $eventUnixtime, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("eventUnixtime", $eventUnixtime, $limit, $orderBy, $orderDir);
    }
}
