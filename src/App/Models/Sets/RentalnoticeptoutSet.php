<?php

namespace App\Models\Sets;

use YAPF\Framework\DbObjects\CollectionSet\CollectionSet as CollectionSet;
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
    public function getObjectByField(string $fieldname, $value): ?Rentalnoticeptout
    {
        return parent::getObjectByField($fieldname, $value);
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
     * loadByNoticeLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByNoticeLink(
        int $noticeLink, 
        int $limit = 0, 
        string $orderBy = "id", 
        string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField(
            "noticeLink", 
            $noticeLink, 
            $limit, 
            $orderBy, 
            $orderDir
        );
    }
    /**
     * loadFromNoticeLinks
     * @return array<mixed> [status =>  bool, count => integer, message =>  string]
    */
    public function loadFromNoticeLinks(array $values): array
    {
        return $this->loadIndexs("noticeLink", $values);
    }
    // Related loaders
    public function relatedNotice(): NoticeSet
    {
        $ids = $this->uniqueNoticeLinks();
        $collection = new NoticeSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedRental(): RentalSet
    {
        $ids = $this->uniqueRentalLinks();
        $collection = new RentalSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
}
