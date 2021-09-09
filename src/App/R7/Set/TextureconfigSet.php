<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Textureconfig as Textureconfig;

// Do not edit this file, rerun gen.php to update!
class TextureconfigSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Textureconfig");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Textureconfig
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Textureconfig
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Textureconfig
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Textureconfig
    {
        return parent::current();
    }
    // Loaders
    /**
     * loadByName
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByName(
                    string $name, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("name", $name, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByOffline
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByOffline(
                    string $offline, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("offline", $offline, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByWaitOwner
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByWaitOwner(
                    string $waitOwner, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("waitOwner", $waitOwner, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByStockLevels
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByStockLevels(
                    string $stockLevels, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("stockLevels", $stockLevels, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByMakePayment
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByMakePayment(
                    string $makePayment, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("makePayment", $makePayment, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByInUse
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByInUse(
                    string $inUse, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("inUse", $inUse, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByRenewHere
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByRenewHere(
                    string $renewHere, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("renewHere", $renewHere, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByTreevendWaiting
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByTreevendWaiting(
                    string $treevendWaiting, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("treevendWaiting", $treevendWaiting, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByProxyRenew
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByProxyRenew(
                    string $proxyRenew, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("proxyRenew", $proxyRenew, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByGettingDetails
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByGettingDetails(
                    string $gettingDetails, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("gettingDetails", $gettingDetails, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByRequestDetails
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByRequestDetails(
                    string $requestDetails, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("requestDetails", $requestDetails, $limit, $orderBy, $orderDir);
    }
}
