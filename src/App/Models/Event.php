<?php

namespace App\Models;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Event extends genClass
{
    protected $use_table = "event";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "avatar_uuid" => ["type" => "str", "value" => null],
        "avatar_name" => ["type" => "str", "value" => null],
        "rental_uid" => ["type" => "str", "value" => null],
        "package_uid" => ["type" => "str", "value" => null],
        "event_new" => ["type" => "bool", "value" => 0],
        "event_renew" => ["type" => "bool", "value" => 0],
        "event_expire" => ["type" => "bool", "value" => 0],
        "event_remove" => ["type" => "bool", "value" => 0],
        "unixtime" => ["type" => "int", "value" => null],
        "expire_unixtime" => ["type" => "int", "value" => null],
        "port" => ["type" => "int", "value" => 0],
    ];
    public function getAvatar_uuid(): ?string
    {
        return $this->getField("avatar_uuid");
    }
    public function getAvatar_name(): ?string
    {
        return $this->getField("avatar_name");
    }
    public function getRental_uid(): ?string
    {
        return $this->getField("rental_uid");
    }
    public function getPackage_uid(): ?string
    {
        return $this->getField("package_uid");
    }
    public function getEvent_new(): ?bool
    {
        return $this->getField("event_new");
    }
    public function getEvent_renew(): ?bool
    {
        return $this->getField("event_renew");
    }
    public function getEvent_expire(): ?bool
    {
        return $this->getField("event_expire");
    }
    public function getEvent_remove(): ?bool
    {
        return $this->getField("event_remove");
    }
    public function getUnixtime(): ?int
    {
        return $this->getField("unixtime");
    }
    public function getExpire_unixtime(): ?int
    {
        return $this->getField("expire_unixtime");
    }
    public function getPort(): ?int
    {
        return $this->getField("port");
    }
    /**
    * setAvatar_uuid
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAvatar_uuid(?string $newvalue): array
    {
        return $this->updateField("avatar_uuid", $newvalue);
    }
    /**
    * setAvatar_name
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAvatar_name(?string $newvalue): array
    {
        return $this->updateField("avatar_name", $newvalue);
    }
    /**
    * setRental_uid
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setRental_uid(?string $newvalue): array
    {
        return $this->updateField("rental_uid", $newvalue);
    }
    /**
    * setPackage_uid
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPackage_uid(?string $newvalue): array
    {
        return $this->updateField("package_uid", $newvalue);
    }
    /**
    * setEvent_new
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEvent_new(?bool $newvalue): array
    {
        return $this->updateField("event_new", $newvalue);
    }
    /**
    * setEvent_renew
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEvent_renew(?bool $newvalue): array
    {
        return $this->updateField("event_renew", $newvalue);
    }
    /**
    * setEvent_expire
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEvent_expire(?bool $newvalue): array
    {
        return $this->updateField("event_expire", $newvalue);
    }
    /**
    * setEvent_remove
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEvent_remove(?bool $newvalue): array
    {
        return $this->updateField("event_remove", $newvalue);
    }
    /**
    * setUnixtime
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setUnixtime(?int $newvalue): array
    {
        return $this->updateField("unixtime", $newvalue);
    }
    /**
    * setExpire_unixtime
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setExpire_unixtime(?int $newvalue): array
    {
        return $this->updateField("expire_unixtime", $newvalue);
    }
    /**
    * setPort
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPort(?int $newvalue): array
    {
        return $this->updateField("port", $newvalue);
    }
}
// please do not edit this file