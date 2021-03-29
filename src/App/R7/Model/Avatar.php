<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Avatar extends genClass
{
    protected $use_table = "avatar";
    // Data Design
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "avatarUUID" => ["type" => "str", "value" => null],
        "avatarName" => ["type" => "str", "value" => null],
        "avatarUid" => ["type" => "str", "value" => null],
    ];
    // Getters
    public function getAvatarUUID(): ?string
    {
        return $this->getField("avatarUUID");
    }
    public function getAvatarName(): ?string
    {
        return $this->getField("avatarName");
    }
    public function getAvatarUid(): ?string
    {
        return $this->getField("avatarUid");
    }
    // Setters
    /**
    * setAvatarUUID
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAvatarUUID(?string $newvalue): array
    {
        return $this->updateField("avatarUUID", $newvalue);
    }
    /**
    * setAvatarName
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAvatarName(?string $newvalue): array
    {
        return $this->updateField("avatarName", $newvalue);
    }
    /**
    * setAvatarUid
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAvatarUid(?string $newvalue): array
    {
        return $this->updateField("avatarUid", $newvalue);
    }
    // Loaders
    public function loadByAvatarUUID(string $avatarUUID): bool
    {
        return $this->loadByField("avatarUUID", $avatarUUID);
    }
    public function loadByAvatarName(string $avatarName): bool
    {
        return $this->loadByField("avatarName", $avatarName);
    }
    public function loadByAvatarUid(string $avatarUid): bool
    {
        return $this->loadByField("avatarUid", $avatarUid);
    }
}
// please do not edit this file
