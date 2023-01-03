<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\AvatarSet as AvatarSet;
use App\Models\Sets\TimezonesSet as TimezonesSet;

// Do not edit this file, rerun gen.php to update!
class Slconfig extends genClass
{
    protected $use_table = "slconfig";
    // Data Design
    protected $fields = [
        "id",
        "dbVersion",
        "newResellers",
        "newResellersRate",
        "slLinkCode",
        "clientsListMode",
        "publicLinkCode",
        "hudLinkCode",
        "ownerAvatarLink",
        "datatableItemsPerPage",
        "httpInboundSecret",
        "displayTimezoneLink",
        "hudAllowDiscord",
        "hudDiscordLink",
        "hudAllowGroup",
        "hudGroupLink",
        "hudAllowDetails",
        "hudAllowRenewal",
        "eventsAPI",
        "paymentKey",
        "streamListOption",
        "clientsDisplayServer",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "dbVersion" => ["type" => "str", "value" => "install"],
        "newResellers" => ["type" => "bool", "value" => 0],
        "newResellersRate" => ["type" => "int", "value" => 0],
        "slLinkCode" => ["type" => "str", "value" => null],
        "clientsListMode" => ["type" => "bool", "value" => 0],
        "publicLinkCode" => ["type" => "str", "value" => null],
        "hudLinkCode" => ["type" => "str", "value" => null],
        "ownerAvatarLink" => ["type" => "int", "value" => null],
        "datatableItemsPerPage" => ["type" => "int", "value" => 10],
        "httpInboundSecret" => ["type" => "str", "value" => null],
        "displayTimezoneLink" => ["type" => "int", "value" => 11],
        "hudAllowDiscord" => ["type" => "bool", "value" => 0],
        "hudDiscordLink" => ["type" => "str", "value" => "Not setup yet"],
        "hudAllowGroup" => ["type" => "bool", "value" => 0],
        "hudGroupLink" => ["type" => "str", "value" => "Not setup yet"],
        "hudAllowDetails" => ["type" => "bool", "value" => 0],
        "hudAllowRenewal" => ["type" => "bool", "value" => 0],
        "eventsAPI" => ["type" => "bool", "value" => 0],
        "paymentKey" => ["type" => "str", "value" => null],
        "streamListOption" => ["type" => "int", "value" => 1],
        "clientsDisplayServer" => ["type" => "bool", "value" => 0],
    ];
    // Setters
    /**
    * setDbVersion
    */
    public function setDbVersion(?string $newValue): UpdateReply
    {
        return $this->updateField("dbVersion", $newValue);
    }
    /**
    * setNewResellers
    */
    public function setNewResellers(?bool $newValue): UpdateReply
    {
        return $this->updateField("newResellers", $newValue);
    }
    /**
    * setNewResellersRate
    */
    public function setNewResellersRate(?int $newValue): UpdateReply
    {
        return $this->updateField("newResellersRate", $newValue);
    }
    /**
    * setSlLinkCode
    */
    public function setSlLinkCode(?string $newValue): UpdateReply
    {
        return $this->updateField("slLinkCode", $newValue);
    }
    /**
    * setClientsListMode
    */
    public function setClientsListMode(?bool $newValue): UpdateReply
    {
        return $this->updateField("clientsListMode", $newValue);
    }
    /**
    * setPublicLinkCode
    */
    public function setPublicLinkCode(?string $newValue): UpdateReply
    {
        return $this->updateField("publicLinkCode", $newValue);
    }
    /**
    * setHudLinkCode
    */
    public function setHudLinkCode(?string $newValue): UpdateReply
    {
        return $this->updateField("hudLinkCode", $newValue);
    }
    /**
    * setOwnerAvatarLink
    */
    public function setOwnerAvatarLink(?int $newValue): UpdateReply
    {
        return $this->updateField("ownerAvatarLink", $newValue);
    }
    /**
    * setDatatableItemsPerPage
    */
    public function setDatatableItemsPerPage(?int $newValue): UpdateReply
    {
        return $this->updateField("datatableItemsPerPage", $newValue);
    }
    /**
    * setHttpInboundSecret
    */
    public function setHttpInboundSecret(?string $newValue): UpdateReply
    {
        return $this->updateField("httpInboundSecret", $newValue);
    }
    /**
    * setDisplayTimezoneLink
    */
    public function setDisplayTimezoneLink(?int $newValue): UpdateReply
    {
        return $this->updateField("displayTimezoneLink", $newValue);
    }
    /**
    * setHudAllowDiscord
    */
    public function setHudAllowDiscord(?bool $newValue): UpdateReply
    {
        return $this->updateField("hudAllowDiscord", $newValue);
    }
    /**
    * setHudDiscordLink
    */
    public function setHudDiscordLink(?string $newValue): UpdateReply
    {
        return $this->updateField("hudDiscordLink", $newValue);
    }
    /**
    * setHudAllowGroup
    */
    public function setHudAllowGroup(?bool $newValue): UpdateReply
    {
        return $this->updateField("hudAllowGroup", $newValue);
    }
    /**
    * setHudGroupLink
    */
    public function setHudGroupLink(?string $newValue): UpdateReply
    {
        return $this->updateField("hudGroupLink", $newValue);
    }
    /**
    * setHudAllowDetails
    */
    public function setHudAllowDetails(?bool $newValue): UpdateReply
    {
        return $this->updateField("hudAllowDetails", $newValue);
    }
    /**
    * setHudAllowRenewal
    */
    public function setHudAllowRenewal(?bool $newValue): UpdateReply
    {
        return $this->updateField("hudAllowRenewal", $newValue);
    }
    /**
    * setEventsAPI
    */
    public function setEventsAPI(?bool $newValue): UpdateReply
    {
        return $this->updateField("eventsAPI", $newValue);
    }
    /**
    * setPaymentKey
    */
    public function setPaymentKey(?string $newValue): UpdateReply
    {
        return $this->updateField("paymentKey", $newValue);
    }
    /**
    * setStreamListOption
    */
    public function setStreamListOption(?int $newValue): UpdateReply
    {
        return $this->updateField("streamListOption", $newValue);
    }
    /**
    * setClientsDisplayServer
    */
    public function setClientsDisplayServer(?bool $newValue): UpdateReply
    {
        return $this->updateField("clientsDisplayServer", $newValue);
    }
    // Getters
    public function getDbVersion(): ?string
    {
        return $this->getField("dbVersion");
    }
    public function getNewResellers(): ?bool
    {
        return $this->getField("newResellers");
    }
    public function getNewResellersRate(): ?int
    {
        return $this->getField("newResellersRate");
    }
    public function getSlLinkCode(): ?string
    {
        return $this->getField("slLinkCode");
    }
    public function getClientsListMode(): ?bool
    {
        return $this->getField("clientsListMode");
    }
    public function getPublicLinkCode(): ?string
    {
        return $this->getField("publicLinkCode");
    }
    public function getHudLinkCode(): ?string
    {
        return $this->getField("hudLinkCode");
    }
    public function getOwnerAvatarLink(): ?int
    {
        return $this->getField("ownerAvatarLink");
    }
    public function getDatatableItemsPerPage(): ?int
    {
        return $this->getField("datatableItemsPerPage");
    }
    public function getHttpInboundSecret(): ?string
    {
        return $this->getField("httpInboundSecret");
    }
    public function getDisplayTimezoneLink(): ?int
    {
        return $this->getField("displayTimezoneLink");
    }
    public function getHudAllowDiscord(): ?bool
    {
        return $this->getField("hudAllowDiscord");
    }
    public function getHudDiscordLink(): ?string
    {
        return $this->getField("hudDiscordLink");
    }
    public function getHudAllowGroup(): ?bool
    {
        return $this->getField("hudAllowGroup");
    }
    public function getHudGroupLink(): ?string
    {
        return $this->getField("hudGroupLink");
    }
    public function getHudAllowDetails(): ?bool
    {
        return $this->getField("hudAllowDetails");
    }
    public function getHudAllowRenewal(): ?bool
    {
        return $this->getField("hudAllowRenewal");
    }
    public function getEventsAPI(): ?bool
    {
        return $this->getField("eventsAPI");
    }
    public function getPaymentKey(): ?string
    {
        return $this->getField("paymentKey");
    }
    public function getStreamListOption(): ?int
    {
        return $this->getField("streamListOption");
    }
    public function getClientsDisplayServer(): ?bool
    {
        return $this->getField("clientsDisplayServer");
    }
    // Loaders
    public function loadByDbVersion(string $dbVersion): SingleLoadReply
    {
        return $this->loadByField(
            "dbVersion",
            $dbVersion
        );
    }
    public function loadByNewResellers(bool $newResellers): SingleLoadReply
    {
        return $this->loadByField(
            "newResellers",
            $newResellers
        );
    }
    public function loadByNewResellersRate(int $newResellersRate): SingleLoadReply
    {
        return $this->loadByField(
            "newResellersRate",
            $newResellersRate
        );
    }
    public function loadBySlLinkCode(string $slLinkCode): SingleLoadReply
    {
        return $this->loadByField(
            "slLinkCode",
            $slLinkCode
        );
    }
    public function loadByClientsListMode(bool $clientsListMode): SingleLoadReply
    {
        return $this->loadByField(
            "clientsListMode",
            $clientsListMode
        );
    }
    public function loadByPublicLinkCode(string $publicLinkCode): SingleLoadReply
    {
        return $this->loadByField(
            "publicLinkCode",
            $publicLinkCode
        );
    }
    public function loadByHudLinkCode(string $hudLinkCode): SingleLoadReply
    {
        return $this->loadByField(
            "hudLinkCode",
            $hudLinkCode
        );
    }
    public function loadByOwnerAvatarLink(int $ownerAvatarLink): SingleLoadReply
    {
        return $this->loadByField(
            "ownerAvatarLink",
            $ownerAvatarLink
        );
    }
    public function loadByDatatableItemsPerPage(int $datatableItemsPerPage): SingleLoadReply
    {
        return $this->loadByField(
            "datatableItemsPerPage",
            $datatableItemsPerPage
        );
    }
    public function loadByHttpInboundSecret(string $httpInboundSecret): SingleLoadReply
    {
        return $this->loadByField(
            "httpInboundSecret",
            $httpInboundSecret
        );
    }
    public function loadByDisplayTimezoneLink(int $displayTimezoneLink): SingleLoadReply
    {
        return $this->loadByField(
            "displayTimezoneLink",
            $displayTimezoneLink
        );
    }
    public function loadByHudAllowDiscord(bool $hudAllowDiscord): SingleLoadReply
    {
        return $this->loadByField(
            "hudAllowDiscord",
            $hudAllowDiscord
        );
    }
    public function loadByHudDiscordLink(string $hudDiscordLink): SingleLoadReply
    {
        return $this->loadByField(
            "hudDiscordLink",
            $hudDiscordLink
        );
    }
    public function loadByHudAllowGroup(bool $hudAllowGroup): SingleLoadReply
    {
        return $this->loadByField(
            "hudAllowGroup",
            $hudAllowGroup
        );
    }
    public function loadByHudGroupLink(string $hudGroupLink): SingleLoadReply
    {
        return $this->loadByField(
            "hudGroupLink",
            $hudGroupLink
        );
    }
    public function loadByHudAllowDetails(bool $hudAllowDetails): SingleLoadReply
    {
        return $this->loadByField(
            "hudAllowDetails",
            $hudAllowDetails
        );
    }
    public function loadByHudAllowRenewal(bool $hudAllowRenewal): SingleLoadReply
    {
        return $this->loadByField(
            "hudAllowRenewal",
            $hudAllowRenewal
        );
    }
    public function loadByEventsAPI(bool $eventsAPI): SingleLoadReply
    {
        return $this->loadByField(
            "eventsAPI",
            $eventsAPI
        );
    }
    public function loadByPaymentKey(string $paymentKey): SingleLoadReply
    {
        return $this->loadByField(
            "paymentKey",
            $paymentKey
        );
    }
    public function loadByStreamListOption(int $streamListOption): SingleLoadReply
    {
        return $this->loadByField(
            "streamListOption",
            $streamListOption
        );
    }
    public function loadByClientsDisplayServer(bool $clientsDisplayServer): SingleLoadReply
    {
        return $this->loadByField(
            "clientsDisplayServer",
            $clientsDisplayServer
        );
    }
    public function relatedAvatar(): AvatarSet
    {
        $ids = [$this->getOwnerAvatarLink()];
        $collection = new AvatarSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
    public function relatedTimezones(): TimezonesSet
    {
        $ids = [$this->getDisplayTimezoneLink()];
        $collection = new TimezonesSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
}
// please do not edit this file
