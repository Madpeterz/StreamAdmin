<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Slconfig extends genClass
{
    protected $use_table = "slconfig";
    // Data Design
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
        "apiDefaultEmail" => ["type" => "str", "value" => null],
        "customLogo" => ["type" => "bool", "value" => 0],
        "customLogoBin" => ["type" => "str", "value" => null],
    ];
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
    public function getApiDefaultEmail(): ?string
    {
        return $this->getField("apiDefaultEmail");
    }
    public function getCustomLogo(): ?bool
    {
        return $this->getField("customLogo");
    }
    public function getCustomLogoBin(): ?string
    {
        return $this->getField("customLogoBin");
    }
    // Setters
    /**
    * setDbVersion
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setDbVersion(?string $newvalue): array
    {
        return $this->updateField("dbVersion", $newvalue);
    }
    /**
    * setNewResellers
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setNewResellers(?bool $newvalue): array
    {
        return $this->updateField("newResellers", $newvalue);
    }
    /**
    * setNewResellersRate
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setNewResellersRate(?int $newvalue): array
    {
        return $this->updateField("newResellersRate", $newvalue);
    }
    /**
    * setSlLinkCode
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setSlLinkCode(?string $newvalue): array
    {
        return $this->updateField("slLinkCode", $newvalue);
    }
    /**
    * setClientsListMode
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setClientsListMode(?bool $newvalue): array
    {
        return $this->updateField("clientsListMode", $newvalue);
    }
    /**
    * setPublicLinkCode
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPublicLinkCode(?string $newvalue): array
    {
        return $this->updateField("publicLinkCode", $newvalue);
    }
    /**
    * setHudLinkCode
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setHudLinkCode(?string $newvalue): array
    {
        return $this->updateField("hudLinkCode", $newvalue);
    }
    /**
    * setOwnerAvatarLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setOwnerAvatarLink(?int $newvalue): array
    {
        return $this->updateField("ownerAvatarLink", $newvalue);
    }
    /**
    * setDatatableItemsPerPage
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setDatatableItemsPerPage(?int $newvalue): array
    {
        return $this->updateField("datatableItemsPerPage", $newvalue);
    }
    /**
    * setHttpInboundSecret
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setHttpInboundSecret(?string $newvalue): array
    {
        return $this->updateField("httpInboundSecret", $newvalue);
    }
    /**
    * setDisplayTimezoneLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setDisplayTimezoneLink(?int $newvalue): array
    {
        return $this->updateField("displayTimezoneLink", $newvalue);
    }
    /**
    * setApiDefaultEmail
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setApiDefaultEmail(?string $newvalue): array
    {
        return $this->updateField("apiDefaultEmail", $newvalue);
    }
    /**
    * setCustomLogo
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setCustomLogo(?bool $newvalue): array
    {
        return $this->updateField("customLogo", $newvalue);
    }
    /**
    * setCustomLogoBin
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setCustomLogoBin(?string $newvalue): array
    {
        return $this->updateField("customLogoBin", $newvalue);
    }
    // Loaders
    public function loadByDbVersion(string $dbVersion): bool
    {
        return $this->loadByField("dbVersion", $dbVersion);
    }
    public function loadByNewResellers(bool $newResellers): bool
    {
        return $this->loadByField("newResellers", $newResellers);
    }
    public function loadByNewResellersRate(int $newResellersRate): bool
    {
        return $this->loadByField("newResellersRate", $newResellersRate);
    }
    public function loadBySlLinkCode(string $slLinkCode): bool
    {
        return $this->loadByField("slLinkCode", $slLinkCode);
    }
    public function loadByClientsListMode(bool $clientsListMode): bool
    {
        return $this->loadByField("clientsListMode", $clientsListMode);
    }
    public function loadByPublicLinkCode(string $publicLinkCode): bool
    {
        return $this->loadByField("publicLinkCode", $publicLinkCode);
    }
    public function loadByHudLinkCode(string $hudLinkCode): bool
    {
        return $this->loadByField("hudLinkCode", $hudLinkCode);
    }
    public function loadByOwnerAvatarLink(int $ownerAvatarLink): bool
    {
        return $this->loadByField("ownerAvatarLink", $ownerAvatarLink);
    }
    public function loadByDatatableItemsPerPage(int $datatableItemsPerPage): bool
    {
        return $this->loadByField("datatableItemsPerPage", $datatableItemsPerPage);
    }
    public function loadByHttpInboundSecret(string $httpInboundSecret): bool
    {
        return $this->loadByField("httpInboundSecret", $httpInboundSecret);
    }
    public function loadByDisplayTimezoneLink(int $displayTimezoneLink): bool
    {
        return $this->loadByField("displayTimezoneLink", $displayTimezoneLink);
    }
    public function loadByApiDefaultEmail(string $apiDefaultEmail): bool
    {
        return $this->loadByField("apiDefaultEmail", $apiDefaultEmail);
    }
    public function loadByCustomLogo(bool $customLogo): bool
    {
        return $this->loadByField("customLogo", $customLogo);
    }
    public function loadByCustomLogoBin(string $customLogoBin): bool
    {
        return $this->loadByField("customLogoBin", $customLogoBin);
    }
}
// please do not edit this file
