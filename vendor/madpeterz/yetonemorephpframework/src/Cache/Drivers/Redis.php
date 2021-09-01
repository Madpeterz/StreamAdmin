<?php

namespace YAPF\Cache\Drivers;

use Exception;
use Predis\Client as RedisClient;
use Predis\Connection\ConnectionException;
use YAPF\Cache\Cache;
use YAPF\Cache\CacheInterface;

class Redis extends Cache implements CacheInterface
{
    protected $tempStorage = [];
    // writes cache to mem first, and then to disk at the end
    // saves unneeded writes if we make a change after loading.
    protected ?RedisClient $client;
    protected int $serverTimeout = 2;
    protected array $connectionSettings = [];
    protected bool $enabled = false;

    public function setTimeout(int $newTimeoutValue = 4): void
    {
        $this->serverTimeout = $newTimeoutValue;
    }

    public function shutdown(): void
    {
        parent::shutdown();
        if ($this->enabled == true) {
            $this->client->disconnect();
        }
    }


    public function connectUnix(string $unixSocket): bool
    {
        $this->setConnectionSettings([
            'scheme' => 'unix',
            'path' => $unixSocket,
            'timeout' => $this->serverTimeout,
            'read_write_timeout' => $this->serverTimeout,
        ]);
        return $this->connectNow();
    }

    public function connectTCP(string $serverIP, int $serverPort = 6379): bool
    {
        $this->setConnectionSettings([
            'scheme' => 'tcp',
            'host'   => $serverIP,
            'port'   => $serverPort,
            'timeout' => $this->serverTimeout,
            'read_write_timeout' => $this->serverTimeout,
        ]);
        return $this->connectNow();
    }

    public function setConnectionSettings(array $settings): void
    {
        $this->connectionSettings = $settings;
    }

    protected function connectNow(): bool
    {
        try {
            $this->client = new RedisClient($this->connectionSettings);
            $this->client->connect();
            $this->enabled = true;
            $this->client->pipeline();
            return true;
        } catch (ConnectionException $ex) {
            $this->addErrorlog("Redis failed: " . $ex->getMessage());
            return false;
        }
    }

    protected function setupCache(): void
    {
        $this->addErrorlog("Cache server: Redis - Please dont fuck it up");
    }

    protected function hasKey(string $key): bool
    {
        if ($this->enabled == false) {
            return false;
        }
        if (in_array($key, $this->seenKeys) == true) {
            return true;
        }
        try {
            if ($this->client->exists($key) == 1) {
                $this->seenKeys[] = $key;
                return true;
            }
        } catch (Exception $ex) {
            $this->addErrorlog("hasKey error: " . $ex->getMessage());
        }
        return false;
    }

    protected function deleteKey(string $key): bool
    {
        if ($this->enabled == false) {
            $this->addErrorlog("[deleteKey] Skipped redis is not connected");
            return false;
        }
        if ($this->hasKey($key) == false) {
            $this->addErrorlog("[deleteKey] Skipped " . $key . " its not found");
            return true;
        }
        if (in_array($key, $this->seenKeys) == true) {
            unset($this->seenKeys[$key]);
        }
        try {
            if ($this->client->del($key) == 1) {
                $this->addErrorlog("[deleteKey] Removed key " . $key . " from server");
                return true;
            }
            $this->addErrorlog("[deleteKey] failed to remove " . $key . " from server");
        } catch (Exception $ex) {
            $this->addErrorlog("[deleteKey] deleteKey error: " . $ex->getMessage());
        }
        return false;
    }

    protected function writeKeyReal(string $key, string $data, string $table, int $expiresUnixtime): bool
    {
        if ($this->enabled == false) {
            $this->addErrorlog("writeKeyReal: error redis not connected");
            return false;
        }
        try {
            $reply = $this->client->set($key, $data, 'EXAT', $expiresUnixtime);
            $this->addErrorlog("writeKeyReal: " . $reply . " for " . $key);
            if ($reply == "OK") {
                return true;
            }
        } catch (Exception $ex) {
            $this->addErrorlog("writeKeyReal error: " . $ex->getMessage());
        }
        return false;
    }

    protected function readKey(string $key): ?string
    {
        if ($this->enabled == false) {
            return null;
        }
        try {
            return $this->client->get($key);
        } catch (Exception $ex) {
            $this->addErrorlog("readKey error: " . $ex->getMessage());
        }
        return null;
    }

    public function purge($attempts = 0): bool
    {
        if ($this->enabled == false) {
            $this->addErrorlog("Redis unable to purge its not connected");
            return false;
        }
        $keys = $this->getKeys();
        if ($keys == null) {
            $this->addErrorlog("Redis unable to get keys");
            return false;
        }
        if ((count($keys) == 0) || ($attempts > 3)) {
            $this->addErrorlog("Redis should be clean now");
            return true;
        }
        foreach ($keys as $key) {
            $this->deleteKey($key);
        }
        sleep(1);
        return $this->purge($attempts + 1);
    }

    /**
     * getKeys
     * returns null on failed, otherwise an array of keys
     * @return mixed[]
     */
    public function getKeys(): ?array
    {
        if ($this->enabled == false) {
            return null;
        }
        try {
            $reply = $this->client->keys("*");
            if ($reply != null) {
                $this->seenKeys = $reply;
            }
            return $reply;
        } catch (Exception $ex) {
            $this->addErrorlog("readKey error: " . $ex->getMessage());
        }
        return [];
    }
}
