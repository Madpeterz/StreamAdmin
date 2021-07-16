<?php

namespace YAPF\Cache;

// Sadly this is not PSR-6

interface CacheInterface
{
    public function __destruct();

    public function start(bool $selfCleanup = false): void;

    public function cleanup(int $max_counter = 5): void;

    public function shutdown(): void;

    public function purge(): bool;

    public function setAccountHash(string $acHash): void;

    public function addTableToCache(string $tablename, int $autoExpireMins = 15, bool $sharedDataset = false): void;

    public function markChangeToTable(string $tableName): void;

    public function cacheVaild(string $tableName, string $hash): bool;

    public function getChangeID(string $tableName): int;

     /**
     * getKeys
     * returns null on failed, otherwise an array of keys
     * @return mixed[]
     */
    public function getKeys(): ?array;

    /*
        - please do not use this function -
        this function is used for testing only
        usage in the real world is not advised.

        this function maybe renamed or remove at any time.
        - please do not use this function -
    */
    public function forceWrite(string $tableName, string $hash, string $info, string $data, int $expires): void;

    /**
     * readHash
     * attempts to read the cache for the selected mapping.
     * @return mixed[] [id => [key => value,...], ...]
    */
    public function readHash(string $tableName, string $hash): ?array;
}
