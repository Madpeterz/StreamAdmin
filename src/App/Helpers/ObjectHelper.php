<?php

namespace App\Helpers;

use App\Models\Objects;

class ObjectHelper
{
    protected $object = null;
    protected $whyfailed = "";
    public function getLastWhyFailed(): string
    {
        return $this->whyfailed;
    }
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
        if (nullSafeStrLen($objectUUID) != 36) {
            return false;
        }
        if ($this->object->loadByObjectUUID($objectUUID)->status == false) {
            $this->object = new Objects();
            $this->object->setAvatarLink($avatar_id);
            $this->object->setRegionLink($region_id);
            $this->object->setObjectUUID($objectUUID);
            $this->object->setObjectName($objectName);
            $this->object->setObjectMode($objectMode);
            $this->object->setObjectXYZ($pos);
            $this->object->setLastSeen(time());
            $save_status = $this->object->createEntry();
            $this->whyfailed = $save_status->message;
            return $save_status->status;
        }
        if ($objectName != $this->object->getObjectName()) {
            $this->object->setObjectName($objectName);
        }
        if ($this->object->getRegionLink() != $region_id) {
            $this->object->setRegionLink($region_id);
        }
        if ($this->updateLastSeen() == false) {
            return false;
        }
        $this->whyfailed = "Current";
        return true;
    }
    public function updateLastSeen(): bool
    {
        if ($this->object->getLastSeen() != time()) {
            $this->object->setLastSeen(time());
            $save_status = $this->object->updateEntry();
            $this->whyfailed = $save_status->message;
            return $save_status->status;
        }
        return true;
    }
}
