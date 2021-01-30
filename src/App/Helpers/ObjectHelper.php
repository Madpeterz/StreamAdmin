<?php

namespace App\Helpers;

use App\R7\Model\Objects;

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
        string $objectUUID,
        string $objectName,
        string $objectMode,
        string $pos
    ): bool {
        $this->object = new Objects();
        if (strlen($objectUUID) != 36) {
            return false;
        }
        if ($this->object->loadByField("objectUUID", $objectUUID) == false) {
            $this->object = new Objects();
            $this->object->setAvatarLink($avatar_id);
            $this->object->setRegionLink($region_id);
            $this->object->setObjectUUID($objectUUID);
            $this->object->setObjectName($objectName);
            $this->object->setObjectMode($objectMode);
            $this->object->setObjectXYZ($pos);
            $this->object->setLastSeen(time());
            $save_status = $this->object->createEntry();
            return $save_status["status"];
        }
        $this->object->setLastSeen(time());
        if ($objectName != $this->object->getObjectName()) {
            $this->object->setObjectName($objectName);
        }
        if ($this->object->getRegionLink() != $region_id) {
            $this->object->setRegionLink($region_id);
        }
        $save_status = $this->object->updateEntry();
        return $save_status["status"];
    }
}
