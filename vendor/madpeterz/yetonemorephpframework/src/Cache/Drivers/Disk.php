<?php

namespace YAPF\Cache\Drivers;

use YAPF\Cache\Cache;
use YAPF\Cache\CacheInterface;

class Disk extends Cache implements CacheInterface
{

    public function __construct(
        string $cacheFolder = "cacheTmp"
    ) {
        $this->allowCleanup = true;
        $this->splitter = "/";
        $this->pathStarting = $cacheFolder;
    }

    protected function setupCache(): void
    {
        $this->addErrorlog("Cache folder:" . $this->pathStarting);
        if (is_dir($this->pathStarting) == false) {
            mkdir($this->pathStarting, 0760, true);
        }
    }

    protected function hasKey(string $key): bool
    {
        if (in_array($key, $this->seenKeys) == true) {
            return true;
        }
        $this->addErrorlog("Checking cache file: " . $key);
        $status = file_exists($key);
        if ($status == true) {
            $this->seenKeys[] = $key;
        }
        return $status;
    }

    protected function deleteKey(string $key): bool
    {
        if (in_array($key, $this->seenKeys) == true) {
            unset($this->seenKeys[$key]);
        }
        if (file_exists($key) == true) {
            return unlink($key);
        }
        return true;
    }

    protected function writeKeyReal(string $key, string $data, string $table, int $expiresUnixtime): bool
    {
        if ($this->deleteKey($key) == false) {
            return false;
        }
        $bits = explode("/", $key);
        array_pop($bits);
        $ubit = "";
        $addon = "";
        foreach ($bits as $bit) {
            $ubit .= $addon;
            $ubit .= $bit;
            if (is_dir($ubit) == false) {
                mkdir($ubit, 0760, true);
            }
            $addon = "/";
        }
        $this->addErrorlog("Writing cache file: " . $key);
        $writeFile = file_put_contents($key, $data);
        if ($writeFile === false) {
            return false;
        }
        return true;
    }

    protected function readKey(string $key): ?string
    {
        if ($this->hasKey($key) == true) {
            $this->addErrorlog("readKey: " . $key);
            return file_get_contents($key);
        }
        return null;
    }

    public function purge(): bool
    {
        $keys = $this->getKeys();
        if ($keys == null) {
            $this->addErrorlog("[purge] Disk failed to get keys");
            return false;
        }
        foreach ($keys as $key) {
            $this->removeKey($key); // deleteKey for redis, removeKey for disk due to the way keys are returned.
        }
        $this->cleanFolders($this->pathStarting);
        $this->addErrorlog("[purge] Disk should be clean now");
        return true;
    }

    protected function cleanFolders($folder): void
    {
        $scan = scandir($folder);
        $working_path = $folder . "/";
        $folder_busy = false;
        foreach ($scan as $file) {
            if ($file == "..") {
                continue;
            } elseif ($file == ".") {
                continue;
            }
            $folder_busy = true;
            if (is_dir($working_path . $file) == true) {
                $this->cleanFolders($working_path . $file);
            }
        }

        if ($folder_busy == false) {
            rmdir($folder);
        }
    }

    /**
     * getKeys
     * returns null on failed, otherwise an array of keys
     * @return mixed[]
     */
    public function getKeys(): ?array
    {
        $reply = $this->mapKeysInFolder($this->pathStarting);
        if ($reply != null) {
            $this->seenKeys = $reply;
        }
        return $reply;
    }

    /**
     * mapKeysInFolder
     * helper function for getKeys for DiskCache only
     * @return string[]
    */
    private function mapKeysInFolder(string $folder): ?array
    {
        $results = [];
        if (is_dir($folder) == false) {
            return null;
        }
        $scan = scandir($folder);
        $working_path = $folder . "/";
        foreach ($scan as $file) {
            if ($file == "..") {
                continue;
            } elseif ($file == ".") {
                continue;
            }
            if (is_dir($working_path . $file) == true) {
                 $results = array_merge($this->mapKeysInFolder($working_path . $file), $results);
            }
            $ending = substr($file, -4);
            if ($ending == ".dat") {
                $filepart = explode(".", $file);
                $results[] = $working_path . $filepart[0];
            }
        }
        return $results;
    }
}
