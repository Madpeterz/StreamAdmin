<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Rental as Rental;

// Do not edit this file, rerun gen.php to update!
class RentalSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Rental");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Rental
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Rental
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Rental
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Rental
    {
        return parent::current();
    }
    // Loaders
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByAvatarLink(int $avatarLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("avatarLink", $avatarLink, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByStreamLink(int $streamLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("streamLink", $streamLink, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByPackageLink(int $packageLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("packageLink", $packageLink, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByNoticeLink(int $noticeLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("noticeLink", $noticeLink, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByStartUnixtime(int $startUnixtime, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("startUnixtime", $startUnixtime, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByExpireUnixtime(int $expireUnixtime, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("expireUnixtime", $expireUnixtime, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByRenewals(int $renewals, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("renewals", $renewals, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByTotalAmount(int $totalAmount, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("totalAmount", $totalAmount, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByMessage(string $message, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("message", $message, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByRentalUid(string $rentalUid, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("rentalUid", $rentalUid, $limit, $orderBy, $orderDir);
    }
}
