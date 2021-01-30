<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Staff extends genClass
{
    protected $use_table = "staff";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "username" => ["type" => "str", "value" => null],
        "email" => ["type" => "str", "value" => null],
        "emailResetCode" => ["type" => "str", "value" => null],
        "emailResetExpires" => ["type" => "int", "value" => 0],
        "avatarLink" => ["type" => "int", "value" => null],
        "phash" => ["type" => "str", "value" => null],
        "lhash" => ["type" => "str", "value" => null],
        "psalt" => ["type" => "str", "value" => null],
        "ownerLevel" => ["type" => "bool", "value" => 0],
    ];
    public function getUsername(): ?string
    {
        return $this->getField("username");
    }
    public function getEmail(): ?string
    {
        return $this->getField("email");
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
    /**
    * setUsername
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setUsername(?string $newvalue): array
    {
        return $this->updateField("username", $newvalue);
    }
    /**
    * setEmail
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEmail(?string $newvalue): array
    {
        return $this->updateField("email", $newvalue);
    }
    /**
    * setEmailResetCode
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEmailResetCode(?string $newvalue): array
    {
        return $this->updateField("emailResetCode", $newvalue);
    }
    /**
    * setEmailResetExpires
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setEmailResetExpires(?int $newvalue): array
    {
        return $this->updateField("emailResetExpires", $newvalue);
    }
    /**
    * setAvatarLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAvatarLink(?int $newvalue): array
    {
        return $this->updateField("avatarLink", $newvalue);
    }
    /**
    * setPhash
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPhash(?string $newvalue): array
    {
        return $this->updateField("phash", $newvalue);
    }
    /**
    * setLhash
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setLhash(?string $newvalue): array
    {
        return $this->updateField("lhash", $newvalue);
    }
    /**
    * setPsalt
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setPsalt(?string $newvalue): array
    {
        return $this->updateField("psalt", $newvalue);
    }
    /**
    * setOwnerLevel
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setOwnerLevel(?bool $newvalue): array
    {
        return $this->updateField("ownerLevel", $newvalue);
    }
}
// please do not edit this file
