<?php

namespace App\R7\Model;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Message extends genClass
{
    protected $use_table = "message";
    // Data Design
    protected $fields = [
        "id",
        "avatarLink",
        "message",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "avatarLink" => ["type" => "int", "value" => null],
        "message" => ["type" => "str", "value" => null],
    ];
    // Getters
    public function getAvatarLink(): ?int
    {
        return $this->getField("avatarLink");
    }
    public function getMessage(): ?string
    {
        return $this->getField("message");
    }
    // Setters
    /**
    * setAvatarLink
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setAvatarLink(?int $newvalue): array
    {
        return $this->updateField("avatarLink", $newvalue);
    }
    /**
    * setMessage
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setMessage(?string $newvalue): array
    {
        return $this->updateField("message", $newvalue);
    }
    // Loaders
    public function loadByAvatarLink(int $avatarLink): bool
    {
        return $this->loadByField("avatarLink", $avatarLink);
    }
    public function loadByMessage(string $message): bool
    {
        return $this->loadByField("message", $message);
    }
}
// please do not edit this file
