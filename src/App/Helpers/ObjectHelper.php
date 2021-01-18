<?php

namespace App\Helpers;

use App\Models\Objects;

class ObjectHelper
{
    protected $object = null;
    public function getObject(): Objects
    {
        return $this->object;
    }
    public function loadOrCreate(
        int $avatar_id,
        int $region_id,
        string $objectuuid,
        string $objectname,
        string $objectmode,
        string $pos
    ): bool {
        $this->object = new Objects();
        if (strlen($objectuuid) != 36) {
            return false;
        }
        if ($this->object->loadByField("objectuuid", $objectuuid) == false) {
            $this->object = new Objects();
            $this->object->setAvatarlink($avatar_id);
            $this->object->setRegionlink($region_id);
            $this->object->setObjectuuid($objectuuid);
            $this->object->setObjectname($objectname);
            $this->object->setObjectmode($objectmode);
            $this->object->setObjectxyz($pos);
            $this->object->setLastseen(time());
            $save_status = $this->object->createEntry();
            return $save_status["status"];
        }
        $this->object->setLastseen(time());
        if ($objectname != $this->object->getObjectname()) {
            $this->object->setObjectname($objectname);
        }
        if ($this->object->getRegionlink() != $region_id) {
            $this->object->setRegionlink($region_id);
        }
        $save_status = $this->object->updateEntry();
        return $save_status["status"];
    }
}
