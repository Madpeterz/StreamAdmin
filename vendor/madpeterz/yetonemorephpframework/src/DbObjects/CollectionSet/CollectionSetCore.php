<?php

namespace YAPF\DbObjects\CollectionSet;

use Error;
use YAPF\Cache\Cache;
use YAPF\Core\SQLi\SqlConnectedClass as SqlConnectedClass;
use YAPF\DbObjects\GenClass\GenClass;

abstract class CollectionSetCore extends SqlConnectedClass
{
    protected array $collected = [];
    protected $indexs = [];
    protected ?string $worker_class = null;
    protected ?GenClass $worker = null;
    protected ?Cache $cache = null;
    protected bool $cacheAllowChanged = false;

    protected bool $disableUpdates = false;
    protected ?array $limitedFields = null;

    /**
     * __construct
     * sets up the worker class
     * by taking the assigned collection name
     * example: TreeCollectionSet
     * removing: CollectionSet
     * to get: Tree as the base class for this collection
     */
    public function __construct(string $worker_class)
    {
        global $cache;
        if (isset($cache) == true) {
            $this->cache = $cache;
        }
        $this->worker_class = $worker_class;
        parent::__construct();
    }

    /**
     * countInDB
     * $where_config: see selectV2.readme
     * Requires a id field
     * @return ?int  returns the count or null if failed
     */
    public function countInDB(?array $whereConfig = null): ?int
    {
        $this->makeWorker();
        // Cache support
        $hitCache = false;
        $hashme = "";
        if ($this->cache != null) {
            $hashme = $this->cache->getHash(
                $whereConfig,
                ["countDB" => "yep"],
                ["countDB" => "yep"],
                ["countDB" => "yep"],
                $this->worker->getTable(),
                count($this->worker->getFields())
            );
            $hitCache = $this->cache->cacheVaild($this->worker->getTable(), $hashme);
        }

        $reply = [];
        $loadedFromCache = false;
        if ($hitCache == true) {
            $reply = $this->cache->readHash($this->worker->getTable(), $hashme);
            if (is_array($reply) == true) {
                $loadedFromCache = true;
            }
        }
        if ($loadedFromCache == false) {
            $reply = $this->sql->basicCountV2($this->worker->getTable(), $whereConfig);
            if (($this->cache != null) && ($reply["status"] == true)) {
                // push data to cache so we can avoid reading from DB as much
                $this->cache->writeHash($this->worker->getTable(), $hashme, $reply, false);
            }
        }
        if ($reply["status"] == false) {
            $this->addError(__FILE__, __FUNCTION__, $reply["message"]);
            return null;
        }
        return $reply["count"];
    }

    public function limitFields(array $fields): void
    {
        $this->makeWorker();
        if (in_array($this->worker->use_id_field, $fields) == false) {
            $fields = array_merge([$this->worker->use_id_field], $fields);
        }
        $this->limitedFields = $fields;
        $this->disableUpdates = true;
    }
    public function getUpdatesStatus(): bool
    {
        return $this->disableUpdates;
    }

    protected function rebuildIndex(): void
    {
        $this->indexs = array_keys($this->collected);
    }

    public function attachCache(Cache $forceAttach): void
    {
        $this->cache = $forceAttach;
    }
    public function setCacheAllowChanged(bool $status = true): void
    {
        $this->cacheAllowChanged = $status;
    }
    /**
     * makeWorker
     * creates the worker object for the collection set
     * if one has not already been created.
     */
    protected function makeWorker(): void
    {
        if ($this->worker == null) {
            $this->worker = new $this->worker_class();
        }
    }
    /**
     * addToCollected
     * adds an object to the collected array
     * using its id as the index.
     */
    public function addToCollected($object): void
    {
        $this->collected[$object->getId()] = $object;
        $this->rebuildIndex();
    }

    protected $fastObjectArrayIndex = [];
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
        if ((in_array($fieldname, $this->fastObjectArrayIndex) == false) || ($force_rebuild == true)) {
            $loadstring = "get" . ucfirst($fieldname);
            if (method_exists($this->worker, $loadstring)) {
                $this->fastObjectArrayIndex[] = $fieldname;
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
