<?php

namespace YAPF\DbObjects\CollectionSet;

abstract class CollectionSetIndex extends CollectionSetCore
{
    protected $fast_get_object_array_indexs = [];
    protected $fast_get_object_array_dataset = [];
    /**
     * buildObjectGetIndex
     * processes the collected objects and builds a fast index
     * to use when fetching by not the ID.
     * Note: if 2 objects share a value on a field the last checked one will take
     * the spot.
     * example:
     * A = 5
     * B = 3
     * C = 5
     * object with index 5 would return C and not A
     * for objects with the bad_id flag
     * the object is stored and not the ID
     * again please dont use bad_id objects unless you have to
     * they suck so bad [god I hate wordpress]
     */
    public function buildObjectGetIndex(string $fieldname, bool $force_rebuild = false): void
    {
        $this->makeWorker();
        if ((in_array($fieldname, $this->fast_get_object_array_indexs) == false) || ($force_rebuild == true)) {
            $loadstring = "get_" . $fieldname;
            if (method_exists($this->worker, $loadstring)) {
                $this->fast_get_object_array_indexs[] = $fieldname;
                $index = [];
                foreach ($this->collected as $key => $object) {
                    if ($this->worker->bad_id == false) {
                        $index[$object->$loadstring()] = $object->getId();
                    } else {
                        $index[$object->$loadstring()] = $object;
                    }
                }
                $this->fast_get_object_array_dataset[$fieldname] = $index;
            }
        }
    }
}
