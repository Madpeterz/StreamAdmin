<?php

namespace App\Models\Sets;

use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\Models\Stream as Stream;

// Do not edit this file, rerun gen.php to update!
class StreamSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Stream");
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
     * uniqueServerLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueServerLinks(): array
    {
        return parent::uniqueArray("serverLink");
    }
    /**
     * uniqueRentalLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueRentalLinks(): array
    {
        return parent::uniqueArray("rentalLink");
    }
    /**
     * uniquePackageLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniquePackageLinks(): array
    {
        return parent::uniqueArray("packageLink");
    }
    /**
     * uniquePorts
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniquePorts(): array
    {
        return parent::uniqueArray("port");
    }
    /**
     * uniqueNeedWorks
     * returns unique values from the collection matching that field
     * @return array<bool>
     */
    public function uniqueNeedWorks(): array
    {
        return parent::uniqueArray("needWork");
    }
    /**
     * uniqueOriginalAdminUsernames
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueOriginalAdminUsernames(): array
    {
        return parent::uniqueArray("originalAdminUsername");
    }
    /**
     * uniqueAdminUsernames
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueAdminUsernames(): array
    {
        return parent::uniqueArray("adminUsername");
    }
    /**
     * uniqueAdminPasswords
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueAdminPasswords(): array
    {
        return parent::uniqueArray("adminPassword");
    }
    /**
     * uniqueDjPasswords
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueDjPasswords(): array
    {
        return parent::uniqueArray("djPassword");
    }
    /**
     * uniqueStreamUids
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueStreamUids(): array
    {
        return parent::uniqueArray("streamUid");
    }
    /**
     * uniqueMountpoints
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueMountpoints(): array
    {
        return parent::uniqueArray("mountpoint");
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
        return $this->loadByField(
            "serverLink", 
            $serverLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromServerLinks
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromServerLinks(array $values): array
    {
        return $this->loadIndexs("serverLink", $values);
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
        return $this->loadByField(
            "rentalLink", 
            $rentalLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromRentalLinks
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromRentalLinks(array $values): array
    {
        return $this->loadIndexs("rentalLink", $values);
    }
    /**
     * loadByPackageLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByPackageLink(
        int $packageLink, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "packageLink", 
            $packageLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromPackageLinks
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromPackageLinks(array $values): array
    {
        return $this->loadIndexs("packageLink", $values);
    }
    /**
     * loadByPort
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByPort(
        int $port, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "port", 
            $port, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromPorts
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromPorts(array $values): array
    {
        return $this->loadIndexs("port", $values);
    }
    /**
     * loadByNeedWork
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByNeedWork(
        bool $needWork, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "needWork", 
            $needWork, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromNeedWorks
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromNeedWorks(array $values): array
    {
        return $this->loadIndexs("needWork", $values);
    }
    /**
     * loadByOriginalAdminUsername
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByOriginalAdminUsername(
        string $originalAdminUsername, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "originalAdminUsername", 
            $originalAdminUsername, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromOriginalAdminUsernames
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromOriginalAdminUsernames(array $values): array
    {
        return $this->loadIndexs("originalAdminUsername", $values);
    }
    /**
     * loadByAdminUsername
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByAdminUsername(
        string $adminUsername, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "adminUsername", 
            $adminUsername, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromAdminUsernames
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromAdminUsernames(array $values): array
    {
        return $this->loadIndexs("adminUsername", $values);
    }
    /**
     * loadByAdminPassword
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByAdminPassword(
        string $adminPassword, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "adminPassword", 
            $adminPassword, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromAdminPasswords
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromAdminPasswords(array $values): array
    {
        return $this->loadIndexs("adminPassword", $values);
    }
    /**
     * loadByDjPassword
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByDjPassword(
        string $djPassword, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "djPassword", 
            $djPassword, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromDjPasswords
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromDjPasswords(array $values): array
    {
        return $this->loadIndexs("djPassword", $values);
    }
    /**
     * loadByStreamUid
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByStreamUid(
        string $streamUid, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "streamUid", 
            $streamUid, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromStreamUids
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromStreamUids(array $values): array
    {
        return $this->loadIndexs("streamUid", $values);
    }
    /**
     * loadByMountpoint
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByMountpoint(
        string $mountpoint, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "mountpoint", 
            $mountpoint, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromMountpoints
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromMountpoints(array $values): array
    {
        return $this->loadIndexs("mountpoint", $values);
    }
    // Related loaders
    public function relatedRental(): RentalSet
    {
        $ids = $this->uniqueIds();
        $collection = new RentalSet();
        $collection->loadFromStreamLinks($ids);
        return $collection;
    }
    public function relatedPackage(): PackageSet
    {
        $ids = $this->uniquePackageLinks();
        $collection = new PackageSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedServer(): ServerSet
    {
        $ids = $this->uniqueServerLinks();
        $collection = new ServerSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedTransactions(): TransactionsSet
    {
        $ids = $this->uniqueIds();
        $collection = new TransactionsSet();
        $collection->loadFromStreamLinks($ids);
        return $collection;
    }
}
