<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Notecardmail as Notecardmail;

// Do not edit this file, rerun gen.php to update!
class NotecardmailSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Notecardmail");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Notecardmail
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Notecardmail
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Notecardmail
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Notecardmail
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
    ): array {
        return $this->loadByField("avatarLink", $avatarLink, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByNoticenotecardLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByNoticenotecardLink(
        int $noticenotecardLink,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): array {
        return $this->loadByField("noticenotecardLink", $noticenotecardLink, $limit, $orderBy, $orderDir);
    }
}
