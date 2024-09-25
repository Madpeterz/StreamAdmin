<?php

namespace App\Models\Sets;

use YAPF\Framework\Responses\DbObjects\SetsLoadReply as SetsLoadReply;
use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use App\Models\Rentalnoticeptout as Rentalnoticeptout;

// Do not edit this file, rerun gen.php to update!
class RentalnoticeptoutSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\Models\Rentalnoticeptout");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Rentalnoticeptout
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Rentalnoticeptout
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldName, $value): ?Rentalnoticeptout
    {
        return parent::getObjectByField($fieldName, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Rentalnoticeptout
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
     * uniqueRentalLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueRentalLinks(): array
    {
        return parent::uniqueArray("rentalLink");
    }
    /**
     * uniqueNoticeLinks
     * returns unique values from the collection matching that field
     * @return array<int>
     */
    public function uniqueNoticeLinks(): array
    {
        return parent::uniqueArray("noticeLink");
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
     * loadByRentalLink
    */
    public function loadByRentalLink(
        int $rentalLink,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "rentalLink",
            $rentalLink,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromRentalLinks
    */
    public function loadFromRentalLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("rentalLink", $values);
    }
    /**
     * loadByNoticeLink
    */
    public function loadByNoticeLink(
        int $noticeLink,
        int $limit = 0,
        string $orderBy = "id",
        string $orderDir = "DESC"
    ): SetsLoadReply {
        return $this->loadOnField(
            "noticeLink",
            $noticeLink,
            $limit,
            $orderBy,
            $orderDir
        );
    }
    /**
     * loadFromNoticeLinks
    */
    public function loadFromNoticeLinks(array $values): SetsLoadReply
    {
        return $this->loadIndexes("noticeLink", $values);
    }
    // Related loaders
    public function relatedNotice(?array $limitFields=null): NoticeSet
    {
        $ids = $this->uniqueNoticeLinks();
        $collection = new NoticeSet();
        if($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedRental(?array $limitFields=null): RentalSet
    {
        $ids = $this->uniqueRentalLinks();
        $collection = new RentalSet();
        if($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
}
