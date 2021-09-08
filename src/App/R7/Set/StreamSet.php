<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Stream as Stream;

// Do not edit this file, rerun gen.php to update!
class StreamSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Stream");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Stream
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Stream
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Stream
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Stream
    {
        return parent::current();
    }
    // Loaders
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByServerLink(int $serverLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("serverLink", $serverLink, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByRentalLink(int $rentalLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("rentalLink", $rentalLink, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByPackageLink(int $packageLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("packageLink", $packageLink, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByPort(int $port, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("port", $port, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByNeedWork(bool $needWork, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("needWork", $needWork, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByOriginalAdminUsername(string $originalAdminUsername, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("originalAdminUsername", $originalAdminUsername, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByAdminUsername(string $adminUsername, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("adminUsername", $adminUsername, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByAdminPassword(string $adminPassword, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("adminPassword", $adminPassword, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByDjPassword(string $djPassword, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("djPassword", $djPassword, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByStreamUid(string $streamUid, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("streamUid", $streamUid, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByMountpoint(string $mountpoint, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("mountpoint", $mountpoint, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByLastApiSync(int $lastApiSync, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("lastApiSync", $lastApiSync, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiConfigValue1(string $apiConfigValue1, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiConfigValue1", $apiConfigValue1, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiConfigValue2(string $apiConfigValue2, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiConfigValue2", $apiConfigValue2, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiConfigValue3(string $apiConfigValue3, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiConfigValue3", $apiConfigValue3, $limit, $orderBy, $orderDir);
    }
}
