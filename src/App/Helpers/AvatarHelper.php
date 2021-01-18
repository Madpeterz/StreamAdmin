<?php

namespace App\Helpers;

use App\Models\Avatar;

class AvatarHelper
{
    protected ?Avatar $avatar = null;
    public function getAvatar(): Avatar
    {
        return $this->avatar;
    }
    public function loadOrCreate(string $avatar_uuid, string $avatar_name): bool
    {
        $this->avatar = new Avatar();
        if (strlen($avatar_uuid) == 36) {
            if ($this->avatar->loadByField("avataruuid", $avatar_uuid) == true) {
                return true;
            }
            $this->avatar = new Avatar();
            $uid = $this->avatar->createUID("avatar_uid", 8, 10);
            if ($uid["status"] == true) {
                $this->avatar->setAvatar_uid($uid["uid"]);
                $this->avatar->setAvatarname($avatar_name);
                $this->avatar->setAvataruuid($avatar_uuid);
                $create_status = $this->avatar->createEntry();
                return $create_status["status"];
            }
        }
        return false;
    }
}
