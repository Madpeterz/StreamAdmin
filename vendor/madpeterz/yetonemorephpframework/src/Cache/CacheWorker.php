<?php

namespace YAPF\Cache;

abstract class CacheWorker extends CacheRequired
{
    protected array $tablesConfig = [];
    protected string $accountHash = "None";
    protected array $tableLastChanged = []; // table => changeID
    protected bool $lastChangedUpdated = false;
    protected string $pathStarting = "";
    protected string $splitter = "-";
    protected array $changedTables = []; // tables found in this array will allways fail cache checks
    protected int $removed_counters = 0;
    protected array $seenKeys = [];
    protected array $keyData = [];
    protected array $keyInfo = [];

    protected function removeKey($key): void
    {
        if (in_array($key, $this->seenKeys) == true) {
            unset($this->seenKeys[$key]);
            unset($this->keyInfo[$key]);
        }
        $this->addErrorlog("Removing key: " . $key);
        $this->deleteKey($key . ".dat");
        $this->deleteKey($key . ".inf");
        $this->removed_counters++;
    }

    protected function saveLastChanged(): void
    {
        $yesno = [true => "Yes", false => "No"];
        $this->addErrorlog("Save last changed: " . $yesno[$this->lastChangedUpdated]);
        if ($this->lastChangedUpdated == true) {
            $this->tableLastChanged["updatedUnixtime"] = time();
            $lastChangedfile = json_encode($this->tableLastChanged);
            $this->addErrorlog("Saving last changed: " . $lastChangedfile);
            $this->writeKey($this->getLastChangedPath(), $lastChangedfile, "lastChanged", time() + (60 * 60));
        }
    }

    protected function writeKey(string $key, string $data, string $table, int $expiresUnixtime): bool
    {
        $tempKey = substr(sha1($key), 0, 6);
        $storage = [
            "key" => $key,
            "data" => $data,
            "table" => $table,
            "versionID" => $this->tableLastChanged[$table],
            "expires" => $expiresUnixtime,
        ];
        $this->tempStorage[$tempKey] = $storage;
        return true;
    }

    private function getLastChangedPath(): string
    {
        return $this->getWorkerPath() . "tables-lastchanged";
    }

    private function getWorkerPath(): string
    {
        $path = "";
        if ($this->pathStarting != "") {
            $path = $this->pathStarting;
            $path .= $this->splitter;
        }
        return $path;
    }

    protected function loadLastChanged(): void
    {
        $this->lastChangedUpdated = true;
        $path = $this->getLastChangedPath();
        if ($this->hasKey($path) == false) {
            $this->addErrorlog("loadLastChanged: missing key");
            return;
        }
        $cacheInfoRead = $this->readKey($path);
        if ($cacheInfoRead == null) {
            $this->addErrorlog("loadLastChanged: key found but data missing");
            return;
        }
        $info_file = json_decode($cacheInfoRead, true);
        if (array_key_exists("updatedUnixtime", $info_file) == false) {
            $this->addErrorlog("loadLastChanged: missing updated unixtime");
            return;
        }
        $dif = time() - $info_file["updatedUnixtime"];
        if ($dif > (60 * 60)) {
            // info dataset is to old to be used
            // everything is marked as changed right now
            $this->addErrorlog("loadLastChanged: to old");
            return;
        }
        foreach (array_keys($this->tablesConfig) as $table) {
            if (array_key_exists($table, $info_file) == true) {
                $this->tableLastChanged[$table] = $info_file[$table];
                $this->addErrorlog("Last changed: setting table: " . $table . " to " . $info_file[$table]);
            }
        }
        $this->lastChangedUpdated = false;
    }

    protected function intLastChanged(): void
    {
        $this->lastChangedUpdated = true;
        foreach (array_keys($this->tablesConfig) as $table) {
            $this->tableLastChanged[$table] = 1;
        }
    }

    /**
     * getHashInfo
     * returns the hash info dataset if found or a empty array
     * @return mixed[] ["unixtime" => int, "expires" => int, "allowChanged" => bool]
    */
    protected function getHashInfo(string $tableName, string $hash): array
    {
        $path = $this->getkeyPath($tableName, $hash);
        $this->addErrorlog("getHashInfo: loading from: " . $path);
        return $this->getKeyInfo($path);
    }

    /**
     * getKeyInfo
     * returns the key info dataset if found or a empty array
     * @return mixed[] ["unixtime" => int, "expires" => int, "allowChanged" => bool]
    */
    protected function getKeyInfo(string $key): array
    {
        if ($this->hasKey($key . ".inf") == false) {
            $this->addErrorlog("getKeyInfo: " . $key . ".inf is missing");
            return []; // cache missing info dataset
        }
        $cacheInfoRead = "";
        if (in_array($key, $this->keyInfo) == true) {
            return json_decode($this->keyInfo[$key], true);
        }
        $cacheInfoRead = $this->readKey($key . ".inf");
        if ($cacheInfoRead == null) {
            $this->addErrorlog("getKeyInfo: read key for info returned nothing");
            return [];
        }
        $this->addErrorlog("getKeyInfo: " . $key . " data: " . $cacheInfoRead);
        $this->keyInfo[$key] = $cacheInfoRead;
        return json_decode($cacheInfoRead, true);
    }

    protected function getkeyPath(string $tableName, string $hash): string
    {
        $use_account_hash = $this->accountHash;
        if ($this->tablesConfig[$tableName]["shared"] == true) {
            $use_account_hash = "None";
        }
        $path = "";
        if ($this->pathStarting != "") {
            $path = $this->pathStarting;
            $path .= $this->splitter;
        }
        $path .= $tableName;
        $path .= $this->splitter;
        $path .= $use_account_hash;
        $path .= $this->splitter;
        $path .= $hash;
        return $path;
    }
}
