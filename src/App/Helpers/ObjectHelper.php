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
            $this->object->_AvatarLink = $avatar_id;
            $this->object->_RegionLink = $region_id;
            $this->object->_ObjectUUID = $objectUUID;
            $this->object->_ObjectName = $objectName;
            $this->object->_ObjectMode = $objectMode;
            $this->object->_ObjectXYZ = $pos;
            $this->object->_LastSeen = time();
            $save_status = $this->object->createEntry();
            $this->whyfailed = $save_status->message;
            return $save_status->status;
        }
        if ($objectName != $this->object->_ObjectName) {
            $this->object->_ObjectName = $objectName;
        }
        if ($this->object->_RegionLink != $region_id) {
            $this->object->_RegionLink = $region_id;
        }
        if ($this->updateLastSeen() == false) {
            return false;
        }
        $this->whyfailed = "Current";
        return true;
    }
    public function updateLastSeen(): bool
    {
        if ($this->object->_LastSeen != time()) {
            $this->object->_LastSeen = time();
            $save_status = $this->object->updateEntry();
            $this->whyfailed = $save_status->message;
            return $save_status->status;
        }
        return true;
    }
}
