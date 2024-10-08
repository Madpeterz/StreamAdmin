<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\AvatarSet as AvatarSet;

// Do not edit this file, rerun gen.php to update!
class Banlist extends genClass
{
    protected $use_table = "banlist";
    // Data Design
    protected $fields = [
        "id",
        "avatarLink",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "avatarLink" => ["type" => "int", "value" => null],
    ];
    // Setters
    /**
    * setAvatarLink
    */
    public function setAvatarLink(?int $newValue): UpdateReply
    {
        return $this->updateField("avatarLink", $newValue);
    }
    // Getters
    public function getAvatarLink(): ?int
    {
        return $this->getField("avatarLink");
    }
    // Loaders
    public function loadByAvatarLink(int $avatarLink): SingleLoadReply
    {
        return $this->loadByField(
            "avatarLink",
            $avatarLink
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
