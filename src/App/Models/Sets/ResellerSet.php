<?php

namespace App\Models\Sets;

use YAPF\Framework\Responses\DbObjects\SetsLoadReply as SetsLoadReply;
use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use App\Models\Reseller as Reseller;

// Do not edit this file, rerun gen.php to update!
class ResellerSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Reseller");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Reseller
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Reseller
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldName, $value): ?Reseller
    {
        return parent::getObjectByField($fieldName, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Reseller
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
     * uniqueAlloweds
     * returns unique values from the collection matching that field
     * @return array<bool>
     */
    public function uniqueAlloweds(): array
    {
        return parent::uniqueArray("allowed");
    }
    /**
     * uniqueRates
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueRates(): array
    {
        return parent::uniqueArray("rate");
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
    ): SetsLoadReply {
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
    ): SetsLoadReply {
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
     * loadByAllowed
    */
    public function loadByAllowed(
        bool $allowed,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "allowed",
            $allowed,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromAlloweds
    */
    public function loadFromAlloweds(array $values): SetsLoadReply
    {
        return $this->loadIndexes("allowed", $values);
    }
    /**
     * loadByRate
    */
    public function loadByRate(
        int $rate,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "rate",
            $rate,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromRates
    */
    public function loadFromRates(array $values): SetsLoadReply
    {
        return $this->loadIndexes("rate", $values);
    }
    // Related loaders
    public function relatedAvatar(?array $limitFields=null): AvatarSet
    {
        $ids = $this->uniqueAvatarLinks();
        $collection = new AvatarSet();
        if($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedTransactions(?array $limitFields=null): TransactionsSet
    {
        $ids = $this->uniqueIds();
        $collection = new TransactionsSet();
        if($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromResellerLinks($ids);
        return $collection;
    }
}
