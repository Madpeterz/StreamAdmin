<?php

namespace YAPF\DbObjects\CollectionSet;

abstract class CollectionSetGet extends CollectionSetIndex
{
    /**
     * getCount
     * returns the number of objects in this collection set
     */
    public function getCount(): int
    {
        return count($this->collected);
    }
    /**
     * getCollection
     * please use: getAllIds and getObjectByID
     * as this mehtod duplicates objects increasing memory usage
     * returns an array of objects in this collection
     * @return object[] [object,...]
     */
    public function getCollection(): array
    {
        return array_values($this->collected);
    }
    /**
     * getLinkedArray
     * returns a key value pair array for all objects in collection
     * example id & name
     * @return mixed[] [leftside_value => rightside_value,...]
     */
    public function getLinkedArray(string $left_side_field, string $right_side_field): array
    {
        $left_side_field_getter = "get" . ucfirst($left_side_field);
        $right_side_field_getter = "get" . ucfirst($right_side_field);
        $worker = new $this->worker_class();
        if (method_exists($worker, $left_side_field_getter) == false) {
            $this->addError(__FILE__, __FUNCTION__, "Field: " . $left_side_field . " is missing");
            return [];
        }
        if (method_exists($worker, $right_side_field_getter) == false) {
            $this->addError(__FILE__, __FUNCTION__, "Field: " . $right_side_field . " is missing");
            return [];
        }
        $return_array = [];
        foreach ($this->collected as $key => $object) {
            if (is_object($object) == true) {
                $return_array[$object->$left_side_field_getter()] = $object->$right_side_field_getter();
            }
        }
        return $return_array;
    }
    /**
     * getUniqueArray
     * gets a Unique array of values based on field_name from
     * the objects.
     * @return mixed[] [value,...]
     */
    public function getUniqueArray(string $field_name): array
    {
        $found_values = [];
        $function = "get" . ucfirst($field_name);
        foreach ($this->collected as $key => $object) {
            if (is_object($object) == true) {
                $value = $object->$function();
                if (in_array($value, $found_values) == false) {
                    $found_values[] = $value;
                }
            }
        }
        return $found_values;
    }
    /**
     * getTable
     * returns the table assigned to the worker
     */
    public function getTable(): string
    {
        $this->makeWorker();
        return $this->worker->getTable();
    }
    /**
     * getIdsMatchingField
     * returns an array of the ids of objects that match the field and value
     * uses the: built_search_index_level_1 to speed up repeated calls.
     * with the same field.
     * @return integer[] [id,...]
     */
    public function getIdsMatchingField(string $fieldname, $fieldvalue): array
    {
        $objects = $this->ObjectIndexSearcher($fieldname, $fieldvalue);
        $ids = [];
        foreach ($objects as $object) {
            $ids[] = $object->getId();
        }
        return $ids;
    }
    /**
     * getFirst
     * returns the first object found in the collection
     * if none are found it returns null
     * @return object or null, object will be of the worker type of the set.
     */
    public function getFirst(): ?object
    {
        $return_obj = null;
        foreach ($this->collected as $key => $value) {
            $return_obj = $value;
            break;
        }
        return $return_obj;
    }
    /**
     * getWorkerClass
     * returns the class name assigned object for this collection
     */
    public function getWorkerClass(): string
    {
        return $this->worker_class;
    }
    /**
     * getCollectionHash
     * returns a sha256 hash of the full collection
     * object hashs
     */
    public function getCollectionHash(): string
    {
        $hash_builder = "";
        foreach ($this->collected as $entry) {
            $hash_builder .= $entry->getHash();
        }
        return hash("sha256", $hash_builder);
    }
    /**
     * getObjectByField
     * Note: Please use getObjectByID if your using the id field
     * as its faster and does not need a index!
     * searchs the index for a object that matchs
     * fieldname to value, if a object shares
     * a value the last loaded one is taken.
     */
    public function getObjectByField(string $fieldname, $value): ?object
    {
        return $this->findObjectByField($fieldname, $value, false);
    }
    /**
     * getObjectByField
     * searchs the index for a object that matchs
     * fieldname to value, if a object shares
     * a value the last entry is used
     */
    protected function findObjectByField(string $fieldname, $value): ?object
    {
        $objects = $this->ObjectIndexSearcher($fieldname, $value);
        if (count($objects) >= 1) {
            return array_pop($objects);
        }
        return null;
    }
    /**
     * getObjectByID
     * returns a object that matchs the selected id
     * returns null if not found
     * Note: Does not support bad Ids please use findObjectByField
     */
    public function getObjectByID($id): ?object
    {
        $this->makeWorker();
        if (array_key_exists($id, $this->collected) == true) {
            return $this->collected[$id];
        }
        return null;
    }
    /**
     * getAllByField
     * alias of getUniqueArray
     * @return mixed[] [value,...]
     */
    public function getAllByField(string $fieldname): array
    {
        return $this->getUniqueArray($fieldname);
    }
    /**
     * getAllIds
     * alias of getUniqueArray
     * defaulted to id or use_id_field
     * @return mixed[] [value,...]
     */
    public function getAllIds(): array
    {
        return $this->getUniqueArray($this->worker->use_id_field);
    }
}
