<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use App\Models\Sets\AvatarSet as AvatarSet;

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
    // Getters
    public function getAvatarLink(): ?int
    {
        return $this->getField("avatarLink");
    }
    public function getMessage(): ?string
    {
        return $this->getField("message");
    }
    // Loaders
    public function loadByAvatarLink(int $avatarLink): bool
    {
        return $this->loadByField(
            "avatarLink",
            $avatarLink
        );
    }
    public function loadByMessage(string $message): bool
    {
        return $this->loadByField(
            "message",
            $message
        );
    }
    public function relatedAvatar(): AvatarSet
    {
        $ids = [$this->getAvatarLink()];
        $collection = new AvatarSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
}
// please do not edit this file
