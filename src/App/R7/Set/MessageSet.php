<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Message as Message;

// Do not edit this file, rerun gen.php to update!
class MessageSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Message");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Message
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Message
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Message
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Message
    {
        return parent::current();
    }
    // Loaders
    /**
     * loadByAvatarLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByAvatarLink(
                    int $avatarLink, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("avatarLink", $avatarLink, $limit, $orderBy, $orderDir);
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
