<?php

namespace App\Helpers;

use App\Models\Avatar;
use YAPF\Core\ErrorControl\ErrorLogging;

class AvatarHelper extends ErrorLogging
{
    protected ?Avatar $avatar = null;
    public function getAvatar(): Avatar
    {
        return $this->avatar;
    }
    public function loadOrCreate(string $avatarUUID, ?string $avatarName = null): bool
    {
        $this->avatar = new Avatar();
        if (strlen($avatarUUID) != 36) {
            return false;
        }
        if ($this->avatar->loadByAvatarUUID($avatarUUID)->status == true) {
            if (($avatarName != null) && ($avatarName != $this->avatar->getAvatarName())) {
                $this->avatar->setAvatarName($avatarName);
                $this->avatar->updateEntry();
            }
            return true;
        }
        if ($avatarName == null) {
            return false;
        }
        $this->avatar = new Avatar();
        $uid = $this->avatar->createUID("avatarUid", 8);
        if ($uid->status == false) {
            return false;
        }
        $this->avatar->setAvatarUid($uid->uid);
        $this->avatar->setAvatarName($avatarName);
        $this->avatar->setAvatarUUID($avatarUUID);
        $create_status = $this->avatar->createEntry();
        if ($create_status->status == false) {
            $this->addError("unable to create avatar: " . $create_status->message);
        }
        return $create_status->status;
    }
}
