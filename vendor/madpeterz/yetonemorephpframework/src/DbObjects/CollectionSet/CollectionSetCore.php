<?php

namespace YAPF\DbObjects\CollectionSet;

use YAPF\Core\SQLi\SqlConnectedClass as SqlConnectedClass;

abstract class CollectionSetCore extends SqlConnectedClass
{
    protected $collected = [];
    protected $worker_class = null;
    protected $worker = null;
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
        $this->worker_class = $worker_class;
        parent::__construct();
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
