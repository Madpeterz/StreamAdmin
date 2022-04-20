<?php

namespace App\Models\Sets;

use YAPF\Framework\Responses\DbObjects\SetsLoadReply as SetsLoadReply;
use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use App\Models\Transactions as Transactions;

// Do not edit this file, rerun gen.php to update!
class TransactionsSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Transactions");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Transactions
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Transactions
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldName, $value): ?Transactions
    {
        return parent::getObjectByField($fieldName, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Transactions
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
     * uniquePackageLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniquePackageLinks(): array
    {
        return parent::uniqueArray("packageLink");
    }
    /**
     * uniqueStreamLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueStreamLinks(): array
    {
        return parent::uniqueArray("streamLink");
    }
    /**
     * uniqueResellerLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueResellerLinks(): array
    {
        return parent::uniqueArray("resellerLink");
    }
    /**
     * uniqueRegionLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueRegionLinks(): array
    {
        return parent::uniqueArray("regionLink");
    }
    /**
     * uniqueAmounts
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueAmounts(): array
    {
        return parent::uniqueArray("amount");
    }
    /**
     * uniqueUnixtimes
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueUnixtimes(): array
    {
        return parent::uniqueArray("unixtime");
    }
    /**
     * uniqueTransactionUids
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueTransactionUids(): array
    {
        return parent::uniqueArray("transactionUid");
    }
    /**
     * uniqueRenews
     * returns unique values from the collection matching that field
     * @return array<bool>
     */
    public function uniqueRenews(): array
    {
        return parent::uniqueArray("renew");
    }
    /**
     * uniqueSLtransactionUUIDs
     * returns unique values from the collection matching that field
     * @return array<string>
     */
    public function uniqueSLtransactionUUIDs(): array
    {
        return parent::uniqueArray("SLtransactionUUID");
    }
    /**
     * uniqueViaHuds
     * returns unique values from the collection matching that field
     * @return array<bool>
     */
    public function uniqueViaHuds(): array
    {
        return parent::uniqueArray("ViaHud");
    }
    // Loaders
    /**
     * loadById
    */
    public function loadById(
        int $id, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "id", 
            $id, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromIds
    */
    public function loadFromIds(array $values): SetsLoadReply
    {
        return $this->loadIndexes("id", $values);
    }
    /**
     * loadByAvatarLink
    */
    public function loadByAvatarLink(
        int $avatarLink, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "avatarLink", 
            $avatarLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromAvatarLinks
    */
    public function loadFromAvatarLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("avatarLink", $values);
    }
    /**
     * loadByPackageLink
    */
    public function loadByPackageLink(
        int $packageLink, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "packageLink", 
            $packageLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromPackageLinks
    */
    public function loadFromPackageLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("packageLink", $values);
    }
    /**
     * loadByStreamLink
    */
    public function loadByStreamLink(
        int $streamLink, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "streamLink", 
            $streamLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromStreamLinks
    */
    public function loadFromStreamLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("streamLink", $values);
    }
    /**
     * loadByResellerLink
    */
    public function loadByResellerLink(
        int $resellerLink, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "resellerLink", 
            $resellerLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromResellerLinks
    */
    public function loadFromResellerLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("resellerLink", $values);
    }
    /**
     * loadByRegionLink
    */
    public function loadByRegionLink(
        int $regionLink, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "regionLink", 
            $regionLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromRegionLinks
    */
    public function loadFromRegionLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("regionLink", $values);
    }
    /**
     * loadByAmount
    */
    public function loadByAmount(
        int $amount, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "amount", 
            $amount, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromAmounts
    */
    public function loadFromAmounts(array $values): SetsLoadReply
    {
        return $this->loadIndexes("amount", $values);
    }
    /**
     * loadByUnixtime
    */
    public function loadByUnixtime(
        int $unixtime, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "unixtime", 
            $unixtime, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromUnixtimes
    */
    public function loadFromUnixtimes(array $values): SetsLoadReply
    {
        return $this->loadIndexes("unixtime", $values);
    }
    /**
     * loadByTransactionUid
    */
    public function loadByTransactionUid(
        string $transactionUid, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "transactionUid", 
            $transactionUid, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromTransactionUids
    */
    public function loadFromTransactionUids(array $values): SetsLoadReply
    {
        return $this->loadIndexes("transactionUid", $values);
    }
    /**
     * loadByRenew
    */
    public function loadByRenew(
        bool $renew, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "renew", 
            $renew, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromRenews
    */
    public function loadFromRenews(array $values): SetsLoadReply
    {
        return $this->loadIndexes("renew", $values);
    }
    /**
     * loadBySLtransactionUUID
    */
    public function loadBySLtransactionUUID(
        string $SLtransactionUUID, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "SLtransactionUUID", 
            $SLtransactionUUID, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromSLtransactionUUIDs
    */
    public function loadFromSLtransactionUUIDs(array $values): SetsLoadReply
    {
        return $this->loadIndexes("SLtransactionUUID", $values);
    }
    /**
     * loadByViaHud
    */
    public function loadByViaHud(
        bool $ViaHud, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): SetsLoadReply
    {
        return $this->loadOnField(
            "ViaHud", 
            $ViaHud, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromViaHuds
    */
    public function loadFromViaHuds(array $values): SetsLoadReply
    {
        return $this->loadIndexes("ViaHud", $values);
    }
    // Related loaders
    public function relatedAvatar(): AvatarSet
    {
        $ids = $this->uniqueAvatarLinks();
        $collection = new AvatarSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedPackage(): PackageSet
    {
        $ids = $this->uniquePackageLinks();
        $collection = new PackageSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedRegion(): RegionSet
    {
        $ids = $this->uniqueRegionLinks();
        $collection = new RegionSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedReseller(): ResellerSet
    {
        $ids = $this->uniqueResellerLinks();
        $collection = new ResellerSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedStream(): StreamSet
    {
        $ids = $this->uniqueStreamLinks();
        $collection = new StreamSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
}
