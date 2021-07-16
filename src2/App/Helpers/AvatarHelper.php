<?php

namespace App\Helpers;

use App\R7\Model\Avatar;

class AvatarHelper
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
        if ($this->avatar->loadByField("avatarUUID", $avatarUUID) == true) {
            if ($avatarName != null) {
                if ($avatarName != $this->avatar->getAvatarName()) {
                    $this->avatar->setAvatarName($avatarName);
                    $this->avatar->updateEntry();
                }
            }
            return true;
        }
        if ($avatarName == null) {
            return false;
        }
        $this->avatar = new Avatar();
        $uid = $this->avatar->createUID("avatarUid", 8, 10);
        if ($uid["status"] == false) {
            return false;
        }
        $this->avatar->setAvatarUid($uid["uid"]);
        $this->avatar->setAvatarName($avatarName);
        $this->avatar->setAvatarUUID($avatarUUID);
        $create_status = $this->avatar->createEntry();
        return $create_status["status"];
    }
}
