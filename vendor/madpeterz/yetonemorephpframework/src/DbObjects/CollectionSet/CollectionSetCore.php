<?php

namespace YAPF\DbObjects\CollectionSet;

use YAPF\Cache\Cache;
use YAPF\Core\SQLi\SqlConnectedClass as SqlConnectedClass;
use YAPF\DbObjects\GenClass\GenClass;

abstract class CollectionSetCore extends SqlConnectedClass
{
    protected array $collected = [];
    protected ?string $worker_class = null;
    protected ?GenClass $worker = null;
    protected ?Cache $cache = null;
    protected bool $cacheAllowChanged = false;
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
    }

    //* - Added so git would save the line ending change
}
