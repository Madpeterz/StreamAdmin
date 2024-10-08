<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\AvatarSet as AvatarSet;
use App\Models\Sets\NoticenotecardSet as NoticenotecardSet;

// Do not edit this file, rerun gen.php to update!
class Notecardmail extends genClass
{
    protected $use_table = "notecardmail";
    // Data Design
    protected $fields = [
        "id",
        "avatarLink",
        "noticenotecardLink",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "avatarLink" => ["type" => "int", "value" => null],
        "noticenotecardLink" => ["type" => "int", "value" => null],
    ];
    // Setters
    /**
    * setAvatarLink
    */
    public function setAvatarLink(?int $newValue): UpdateReply
    {
        return $this->updateField("avatarLink", $newValue);
    }
    /**
    * setNoticenotecardLink
    */
    public function setNoticenotecardLink(?int $newValue): UpdateReply
    {
        return $this->updateField("noticenotecardLink", $newValue);
    }
    // Getters
    public function getAvatarLink(): ?int
    {
        return $this->getField("avatarLink");
    }
    public function getNoticenotecardLink(): ?int
    {
        return $this->getField("noticenotecardLink");
    }
    // Loaders
    public function loadByAvatarLink(int $avatarLink): SingleLoadReply
    {
        return $this->loadByField(
            "avatarLink",
            $avatarLink
        );
    }
    public function loadByNoticenotecardLink(int $noticenotecardLink): SingleLoadReply
    {
        return $this->loadByField(
            "noticenotecardLink",
            $noticenotecardLink
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
    public function relatedNoticenotecard(?array $limitFields = null): NoticenotecardSet
    {
        $ids = [$this->getNoticenotecardLink()];
        $collection = new NoticenotecardSet();
        if ($limitFields !== null) {
            $collection->limitFields($limitFields);
        }
        $collection->loadFromIds($ids);
        return $collection;
    }
}
// please do not edit this file
