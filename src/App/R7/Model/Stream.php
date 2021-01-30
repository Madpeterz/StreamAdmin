<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Stream extends genClass
{
    protected $use_table = "stream";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "serverLink" => ["type" => "int", "value" => null],
        "rentalLink" => ["type" => "int", "value" => null],
        "packageLink" => ["type" => "int", "value" => null],
        "port" => ["type" => "int", "value" => null],
        "needWork" => ["type" => "bool", "value" => 0],
        "originalAdminUsername" => ["type" => "str", "value" => null],
        "adminUsername" => ["type" => "str", "value" => null],
        "adminPassword" => ["type" => "str", "value" => null],
        "djPassword" => ["type" => "str", "value" => null],
        "streamUid" => ["type" => "str", "value" => null],
        "mountpoint" => ["type" => "str", "value" => null],
        "lastApiSync" => ["type" => "int", "value" => 0],
        "apiConfigValue1" => ["type" => "str", "value" => null],
        "apiConfigValue2" => ["type" => "str", "value" => null],
        "apiConfigValue3" => ["type" => "str", "value" => null],
    ];
    public function getServerLink(): ?int
    {
        return $this->getField("serverLink");
    }
    public function getRentalLink(): ?int
    {
        return $this->getField("rentalLink");
    }
    public function getPackageLink(): ?int
    {
        return $this->getField("packageLink");
    }
    public function getPort(): ?int
    {
        return $this->getField("port");
    }
    public function getNeedWork(): ?bool
    {
        return $this->getField("needWork");
    }
    public function getOriginalAdminUsername(): ?string
    {
        return $this->getField("originalAdminUsername");
    }
    public function getAdminUsername(): ?string
    {
        return $this->getField("adminUsername");
    }
    public function getAdminPassword(): ?string
    {
        return $this->getField("adminPassword");
    }
    public function getDjPassword(): ?string
    {
        return $this->getField("djPassword");
    }
    public function getStreamUid(): ?string
    {
        return $this->getField("streamUid");
    }
    public function getMountpoint(): ?string
    {
        return $this->getField("mountpoint");
    }
    public function getLastApiSync(): ?int
    {
        return $this->getField("lastApiSync");
    }
    public function getApiConfigValue1(): ?string
    {
        return $this->getField("apiConfigValue1");
    }
    public function getApiConfigValue2(): ?string
    {
        return $this->getField("apiConfigValue2");
    }
    public function getApiConfigValue3(): ?string
    {
        return $this->getField("apiConfigValue3");
    }
    /**
    * setServerLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setServerLink(?int $newvalue): array
    {
        return $this->updateField("serverLink", $newvalue);
    }
    /**
    * setRentalLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setRentalLink(?int $newvalue): array
    {
        return $this->updateField("rentalLink", $newvalue);
    }
    /**
    * setPackageLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPackageLink(?int $newvalue): array
    {
        return $this->updateField("packageLink", $newvalue);
    }
    /**
    * setPort
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPort(?int $newvalue): array
    {
        return $this->updateField("port", $newvalue);
    }
    /**
    * setNeedWork
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setNeedWork(?bool $newvalue): array
    {
        return $this->updateField("needWork", $newvalue);
    }
    /**
    * setOriginalAdminUsername
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setOriginalAdminUsername(?string $newvalue): array
    {
        return $this->updateField("originalAdminUsername", $newvalue);
    }
    /**
    * setAdminUsername
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAdminUsername(?string $newvalue): array
    {
        return $this->updateField("adminUsername", $newvalue);
    }
    /**
    * setAdminPassword
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAdminPassword(?string $newvalue): array
    {
        return $this->updateField("adminPassword", $newvalue);
    }
    /**
    * setDjPassword
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setDjPassword(?string $newvalue): array
    {
        return $this->updateField("djPassword", $newvalue);
    }
    /**
    * setStreamUid
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setStreamUid(?string $newvalue): array
    {
        return $this->updateField("streamUid", $newvalue);
    }
    /**
    * setMountpoint
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setMountpoint(?string $newvalue): array
    {
        return $this->updateField("mountpoint", $newvalue);
    }
    /**
    * setLastApiSync
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setLastApiSync(?int $newvalue): array
    {
        return $this->updateField("lastApiSync", $newvalue);
    }
    /**
    * setApiConfigValue1
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setApiConfigValue1(?string $newvalue): array
    {
        return $this->updateField("apiConfigValue1", $newvalue);
    }
    /**
    * setApiConfigValue2
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setApiConfigValue2(?string $newvalue): array
    {
        return $this->updateField("apiConfigValue2", $newvalue);
    }
    /**
    * setApiConfigValue3
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setApiConfigValue3(?string $newvalue): array
    {
        return $this->updateField("apiConfigValue3", $newvalue);
    }
}
// please do not edit this file
