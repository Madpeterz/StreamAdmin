<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\AvatarSet as AvatarSet;

// Do not edit this file, rerun gen.php to update!
class Staff extends genClass
{
    protected $use_table = "staff";
    // Data Design
    protected $fields = [
        "id",
        "username",
        "emailResetCode",
        "emailResetExpires",
        "avatarLink",
        "phash",
        "lhash",
        "psalt",
        "ownerLevel",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "username" => ["type" => "str", "value" => null],
        "emailResetCode" => ["type" => "str", "value" => null],
        "emailResetExpires" => ["type" => "int", "value" => 0],
        "avatarLink" => ["type" => "int", "value" => null],
        "phash" => ["type" => "str", "value" => null],
        "lhash" => ["type" => "str", "value" => null],
        "psalt" => ["type" => "str", "value" => null],
        "ownerLevel" => ["type" => "bool", "value" => 0],
    ];
    // Setters
    /**
    * setUsername
    */
    public function setUsername(?string $newValue): UpdateReply
    {
        return $this->updateField("username", $newValue);
    }
    /**
    * setEmailResetCode
    */
    public function setEmailResetCode(?string $newValue): UpdateReply
    {
        return $this->updateField("emailResetCode", $newValue);
    }
    /**
    * setEmailResetExpires
    */
    public function setEmailResetExpires(?int $newValue): UpdateReply
    {
        return $this->updateField("emailResetExpires", $newValue);
    }
    /**
    * setAvatarLink
    */
    public function setAvatarLink(?int $newValue): UpdateReply
    {
        return $this->updateField("avatarLink", $newValue);
    }
    /**
    * setPhash
    */
    public function setPhash(?string $newValue): UpdateReply
    {
        return $this->updateField("phash", $newValue);
    }
    /**
    * setLhash
    */
    public function setLhash(?string $newValue): UpdateReply
    {
        return $this->updateField("lhash", $newValue);
    }
    /**
    * setPsalt
    */
    public function setPsalt(?string $newValue): UpdateReply
    {
        return $this->updateField("psalt", $newValue);
    }
    /**
    * setOwnerLevel
    */
    public function setOwnerLevel(?bool $newValue): UpdateReply
    {
        return $this->updateField("ownerLevel", $newValue);
    }
    // Getters
    public function getUsername(): ?string
    {
        return $this->getField("username");
    }
    public function getEmailResetCode(): ?string
    {
        return $this->getField("emailResetCode");
    }
    public function getEmailResetExpires(): ?int
    {
        return $this->getField("emailResetExpires");
    }
    public function getAvatarLink(): ?int
    {
        return $this->getField("avatarLink");
    }
    public function getPhash(): ?string
    {
        return $this->getField("phash");
    }
    public function getLhash(): ?string
    {
        return $this->getField("lhash");
    }
    public function getPsalt(): ?string
    {
        return $this->getField("psalt");
    }
    public function getOwnerLevel(): ?bool
    {
        return $this->getField("ownerLevel");
    }
    // Loaders
    public function loadByUsername(string $username): SingleLoadReply
    {
        return $this->loadByField(
            "username",
            $username
        );
    }
    public function loadByEmailResetCode(string $emailResetCode): SingleLoadReply
    {
        return $this->loadByField(
            "emailResetCode",
            $emailResetCode
        );
    }
    public function loadByEmailResetExpires(int $emailResetExpires): SingleLoadReply
    {
        return $this->loadByField(
            "emailResetExpires",
            $emailResetExpires
        );
    }
    public function loadByAvatarLink(int $avatarLink): SingleLoadReply
    {
        return $this->loadByField(
            "avatarLink",
            $avatarLink
        );
    }
    public function loadByPhash(string $phash): SingleLoadReply
    {
        return $this->loadByField(
            "phash",
            $phash
        );
    }
    public function loadByLhash(string $lhash): SingleLoadReply
    {
        return $this->loadByField(
            "lhash",
            $lhash
        );
    }
    public function loadByPsalt(string $psalt): SingleLoadReply
    {
        return $this->loadByField(
            "psalt",
            $psalt
        );
    }
    public function loadByOwnerLevel(bool $ownerLevel): SingleLoadReply
    {
        return $this->loadByField(
            "ownerLevel",
            $ownerLevel
        );
    }
    public function relatedAvatar(?array $limitFields = null): AvatarSet
    {
        $ids = [$this->getAvatarLink()];
        $collection = new AvatarSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
}
// please do not edit this file
