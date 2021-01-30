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
    public function loadOrCreate(string $avatarUUID, string $avatarName): bool
    {
        $this->avatar = new Avatar();
        if (strlen($avatarUUID) == 36) {
            if ($this->avatar->loadByField("avatarUUID", $avatarUUID) == true) {
                return true;
            }
            $this->avatar = new Avatar();
            $uid = $this->avatar->createUID("avatarUid", 8, 10);
            if ($uid["status"] == true) {
                $this->avatar->setAvatarUid($uid["uid"]);
                $this->avatar->setAvatarName($avatarName);
                $this->avatar->setAvatarUUID($avatarUUID);
                $create_status = $this->avatar->createEntry();
                return $create_status["status"];
            }
        }
        return false;
    }
}
