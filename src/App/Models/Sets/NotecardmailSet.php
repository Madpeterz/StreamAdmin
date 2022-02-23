<?php

namespace App\Models\Sets;

use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\Models\Notecardmail as Notecardmail;

// Do not edit this file, rerun gen.php to update!
class NotecardmailSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Notecardmail");
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
     * uniqueAvatarLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueAvatarLinks(): array
    {
        return parent::uniqueArray("avatarLink");
    }
    /**
     * uniqueNoticenotecardLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueNoticenotecardLinks(): array
    {
        return parent::uniqueArray("noticenotecardLink");
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
        return $this->loadByField(
            "avatarLink", 
            $avatarLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromAvatarLinks
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromAvatarLinks(array $values): array
    {
        return $this->loadIndexs("avatarLink", $values);
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
    ): array
    {
        return $this->loadByField(
            "noticenotecardLink", 
            $noticenotecardLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromNoticenotecardLinks
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromNoticenotecardLinks(array $values): array
    {
        return $this->loadIndexs("noticenotecardLink", $values);
    }
    // Related loaders
}