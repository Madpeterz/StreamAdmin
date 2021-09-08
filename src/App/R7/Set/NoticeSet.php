<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Notice as Notice;

// Do not edit this file, rerun gen.php to update!
class NoticeSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Notice");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Notice
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Notice
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Notice
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Notice
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
    public function loadByImMessage(string $imMessage, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("imMessage", $imMessage, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByUseBot(bool $useBot, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("useBot", $useBot, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadBySendNotecard(bool $sendNotecard, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("sendNotecard", $sendNotecard, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByNotecardDetail(string $notecardDetail, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("notecardDetail", $notecardDetail, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByHoursRemaining(int $hoursRemaining, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("hoursRemaining", $hoursRemaining, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByNoticeNotecardLink(int $noticeNotecardLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("noticeNotecardLink", $noticeNotecardLink, $limit, $orderBy, $orderDir);
    }
}
