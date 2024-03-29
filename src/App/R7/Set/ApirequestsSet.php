<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Apirequests as Apirequests;

// Do not edit this file, rerun gen.php to update!
class ApirequestsSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Apirequests");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Apirequests
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Apirequests
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Apirequests
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Apirequests
    {
        return parent::current();
    }
    // Loaders
    /**
     * loadByServerLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByServerLink(
                    int $serverLink, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("serverLink", $serverLink, $limit, $orderBy, $orderDir);
    }
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
    /**
     * loadByStreamLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByStreamLink(
                    int $streamLink, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("streamLink", $streamLink, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByEventname
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByEventname(
                    string $eventname, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("eventname", $eventname, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByAttempts
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByAttempts(
                    int $attempts, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("attempts", $attempts, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByLastAttempt
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByLastAttempt(
                    int $lastAttempt, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("lastAttempt", $lastAttempt, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByMessage
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByMessage(
                    string $message, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("message", $message, $limit, $orderBy, $orderDir);
    }
}
