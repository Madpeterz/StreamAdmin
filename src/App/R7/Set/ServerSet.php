<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Server as Server;

// Do not edit this file, rerun gen.php to update!
class ServerSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Server");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Server
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Server
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Server
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Server
    {
        return parent::current();
    }
    // Loaders
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByDomain(string $domain, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("domain", $domain, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByControlPanelURL(string $controlPanelURL, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("controlPanelURL", $controlPanelURL, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiLink(int $apiLink, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiLink", $apiLink, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiURL(string $apiURL, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiURL", $apiURL, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiUsername(string $apiUsername, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiUsername", $apiUsername, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiPassword(string $apiPassword, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiPassword", $apiPassword, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiServerStatus(bool $apiServerStatus, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiServerStatus", $apiServerStatus, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByApiSyncAccounts(bool $apiSyncAccounts, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("apiSyncAccounts", $apiSyncAccounts, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByOptPasswordReset(bool $optPasswordReset, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("optPasswordReset", $optPasswordReset, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByOptAutodjNext(bool $optAutodjNext, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("optAutodjNext", $optAutodjNext, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByOptToggleAutodj(bool $optToggleAutodj, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("optToggleAutodj", $optToggleAutodj, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByOptToggleStatus(bool $optToggleStatus, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("optToggleStatus", $optToggleStatus, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventEnableStart(bool $eventEnableStart, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventEnableStart", $eventEnableStart, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventStartSyncUsername(bool $eventStartSyncUsername, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventStartSyncUsername", $eventStartSyncUsername, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventEnableRenew(bool $eventEnableRenew, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventEnableRenew", $eventEnableRenew, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventDisableExpire(bool $eventDisableExpire, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventDisableExpire", $eventDisableExpire, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventDisableRevoke(bool $eventDisableRevoke, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventDisableRevoke", $eventDisableRevoke, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventRevokeResetUsername(bool $eventRevokeResetUsername, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventRevokeResetUsername", $eventRevokeResetUsername, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventResetPasswordRevoke(bool $eventResetPasswordRevoke, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventResetPasswordRevoke", $eventResetPasswordRevoke, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventClearDjs(bool $eventClearDjs, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventClearDjs", $eventClearDjs, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventRecreateRevoke(bool $eventRecreateRevoke, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventRecreateRevoke", $eventRecreateRevoke, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByLastApiSync(int $lastApiSync, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("lastApiSync", $lastApiSync, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventCreateStream(bool $eventCreateStream, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventCreateStream", $eventCreateStream, $limit, $orderBy, $orderDir);
    }
    //@return mixed[] [status =>  bool, count => integer, message =>  string]
    public function loadByEventUpdateStream(bool $eventUpdateStream, int $limit=0, string $orderBy="id", string $orderDir="DESC"): array
    {
        return $this->loadByField("eventUpdateStream", $eventUpdateStream, $limit, $orderBy, $orderDir);
    }
}
