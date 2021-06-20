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
    protected function buildObjectGetIndex(string $fieldname, bool $force_rebuild = false): void
    {
        $this->makeWorker();
        if ((in_array($fieldname, $this->fast_get_object_array_indexs) == false) || ($force_rebuild == true)) {
            $loadstring = "get" . ucfirst($fieldname);
            if (method_exists($this->worker, $loadstring)) {
                $this->fast_get_object_array_indexs[] = $fieldname;
                $index = [];
                foreach ($this->collected as $key => $object) {
                    $indexValue = $object->$loadstring();
                    if ($indexValue === true) {
                        $indexValue = 1;
                    } elseif ($indexValue == false) {
                        $indexValue = 0;
                    }
                    if (array_key_exists($indexValue, $index) == false) {
                        $index[$indexValue] = [];
                    }
                    $storeitem = $object;
                    if ($this->worker->bad_id == false) {
                        $storeitem = $object->getId();
                    }
                    $index[$indexValue][] = $storeitem;
                }
                $this->fast_get_object_array_dataset[$fieldname] = $index;
            }
        }
    }
    /**
     * objectIndexSearcher
     * returns an array of objects that matched the search settings
     * @return mixed[] [object,...]
    */
    protected function objectIndexSearcher(string $fieldname, $fieldvalue): array
    {
        $this->makeWorker();
        $this->buildObjectGetIndex($fieldname);
        $return_objects = [];
        if (array_key_exists($fieldname, $this->fast_get_object_array_dataset) == true) {
            $loadstring = "get" . ucfirst($fieldname);
            if (method_exists($this->worker, $loadstring)) {
                if (array_key_exists($fieldvalue, $this->fast_get_object_array_dataset[$fieldname]) == true) {
                    $return_objects = $this->fast_get_object_array_dataset[$fieldname][$fieldvalue];
                    if ($this->worker->bad_id == false) {
                        $return_objects = [];
                        foreach ($this->fast_get_object_array_dataset[$fieldname][$fieldvalue] as $objectid) {
                            if (array_key_exists($objectid, $this->collected) == true) {
                                $return_objects[] = $this->collected[$objectid];
                            }
                        }
                    }
                }
            }
        }
        return $return_objects;
    }
}
