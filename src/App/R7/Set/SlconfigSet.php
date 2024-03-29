<?php

namespace App\R7\Set;

use YAPF\DbObjects\CollectionSet\CollectionSet as CollectionSet;
use App\R7\Model\Slconfig as Slconfig;

// Do not edit this file, rerun gen.php to update!
class SlconfigSet extends CollectionSet
{
    public function __construct()
    {
        parent::__construct("App\R7\Model\Slconfig");
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?Slconfig
    {
        return parent::getObjectByID($id);
    }
    /**
     * getFirst
     * returns the first object in a collection
     */
    public function getFirst(): ?Slconfig
    {
        return parent::getFirst();
    }
    /**
     * getObjectByField
     * returns the first object in a collection that matchs the field and value checks
     */
    public function getObjectByField(string $fieldname, $value): ?Slconfig
    {
        return parent::getObjectByField($fieldname, $value);
    }
    /**
     * current
     * used by foreach to get the object should not be called directly
     */
    public function current(): Slconfig
    {
        return parent::current();
    }
    // Loaders
    /**
     * loadByDbVersion
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByDbVersion(
                    string $dbVersion, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("dbVersion", $dbVersion, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByNewResellers
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByNewResellers(
                    bool $newResellers, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("newResellers", $newResellers, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByNewResellersRate
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByNewResellersRate(
                    int $newResellersRate, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("newResellersRate", $newResellersRate, $limit, $orderBy, $orderDir);
    }
    /**
     * loadBySlLinkCode
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadBySlLinkCode(
                    string $slLinkCode, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("slLinkCode", $slLinkCode, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByClientsListMode
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByClientsListMode(
                    bool $clientsListMode, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("clientsListMode", $clientsListMode, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByPublicLinkCode
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByPublicLinkCode(
                    string $publicLinkCode, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("publicLinkCode", $publicLinkCode, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByHudLinkCode
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByHudLinkCode(
                    string $hudLinkCode, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("hudLinkCode", $hudLinkCode, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByOwnerAvatarLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByOwnerAvatarLink(
                    int $ownerAvatarLink, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("ownerAvatarLink", $ownerAvatarLink, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByDatatableItemsPerPage
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByDatatableItemsPerPage(
                    int $datatableItemsPerPage, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("datatableItemsPerPage", $datatableItemsPerPage, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByHttpInboundSecret
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByHttpInboundSecret(
                    string $httpInboundSecret, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("httpInboundSecret", $httpInboundSecret, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByDisplayTimezoneLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByDisplayTimezoneLink(
                    int $displayTimezoneLink, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("displayTimezoneLink", $displayTimezoneLink, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByApiDefaultEmail
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByApiDefaultEmail(
                    string $apiDefaultEmail, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("apiDefaultEmail", $apiDefaultEmail, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByCustomLogo
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByCustomLogo(
                    bool $customLogo, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("customLogo", $customLogo, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByCustomLogoBin
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByCustomLogoBin(
                    string $customLogoBin, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("customLogoBin", $customLogoBin, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByHudAllowDiscord
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByHudAllowDiscord(
                    bool $hudAllowDiscord, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("hudAllowDiscord", $hudAllowDiscord, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByHudDiscordLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByHudDiscordLink(
                    string $hudDiscordLink, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("hudDiscordLink", $hudDiscordLink, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByHudAllowGroup
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByHudAllowGroup(
                    bool $hudAllowGroup, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("hudAllowGroup", $hudAllowGroup, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByHudGroupLink
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByHudGroupLink(
                    string $hudGroupLink, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("hudGroupLink", $hudGroupLink, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByHudAllowDetails
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByHudAllowDetails(
                    bool $hudAllowDetails, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("hudAllowDetails", $hudAllowDetails, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByHudAllowRenewal
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByHudAllowRenewal(
                    bool $hudAllowRenewal, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("hudAllowRenewal", $hudAllowRenewal, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByEventsAPI
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByEventsAPI(
                    bool $eventsAPI, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("eventsAPI", $eventsAPI, $limit, $orderBy, $orderDir);
    }
    /**
     * loadByPaymentKey
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
    */
    public function loadByPaymentKey(
                    string $paymentKey, 
                    int $limit = 0, 
                    string $orderBy = "id", 
                    string $orderDir = "DESC"
    ): array
    {
        return $this->loadByField("paymentKey", $paymentKey, $limit, $orderBy, $orderDir);
    }
}
