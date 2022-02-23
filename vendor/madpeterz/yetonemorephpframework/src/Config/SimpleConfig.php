<?php

namespace YAPF\Framework\Config;

use YAPF\Framework\Cache\Cache;
use YAPF\Framework\Cache\Drivers\Disk;
use YAPF\Framework\Cache\Drivers\Redis;
use YAPF\Core\ErrorControl\ErrorLogging;
use YAPF\Framework\MySQLi\MysqliEnabled;

class SimpleConfig extends ErrorLogging
{
    // Cache
    protected ?Cache $Cache = null;
    protected bool $cache_enabled = false;

    // Cache / Disk
    protected bool $use_disk_cache = false;
    protected string $disk_cache_folder = "cache";

    // Cache / Redis
    protected bool $use_redis_cache = false;

    // Cache / Redis / Unix socket
    protected bool $redisUnix = false;
    protected string $redis_socket = "/var/run/redis/redis.sock";

    // Cache / Redis / TCP
    protected string $redis_host = "redis";
    protected int $redis_port = 6379;
    protected int $redis_timeout = 3;

    // docker flag
    protected bool $dockerConfigLocked = false;

    // SQL connection
    protected ?MysqliEnabled $sql = null;

    public function __construct()
    {
        if (class_exists("App\\Db", true) == false) {
            $offline = [
                "status" => 0,
                "message" => "- Service offline -<br/> DB config missing",
            ];
            die(json_encode($offline));
        }
    }

    public function shutdown(): void
    {
        if ($this->sql != null) {
            $this->sql->shutdown();
            $this->sql = null;
        }
        if ($this->Cache != null) {
            $this->Cache->shutdown();
            $this->Cache = null;
        }
        $this->enableRestart = false;
    }

    protected bool $enableRestart = true;
    /*
        SQL functions
    */
    public function &getSQL(): ?MysqliEnabled
    {
        if (($this->sql == null) && ($this->enableRestart == true)) {
            $this->sql = new MysqliEnabled();
        }
        return $this->sql;
    }

    /*
        Cache functions
    */
    public function &getCacheDriver(): ?Cache
    {
        if (($this->Cache == null) && ($this->enableRestart == true)) {
            $this->setupCache();
            if ($this->Cache != null) {
                $this->startCache();
            }
        }
        return $this->Cache;
    }

    public function configCacheDisabled(): void
    {
        if ($this->dockerConfigLocked == true) {
            return;
        }
        $this->use_redis_cache = false;
        $this->use_disk_cache = false;
    }
    public function configCacheRedisUnixSocket(string $socket = "/var/run/redis/redis.sock"): void
    {
        if ($this->dockerConfigLocked == true) {
            return;
        }
        $this->use_redis_cache = true;
        $this->redisUnix = true;
        $this->redis_socket = $socket;
    }
    public function configCacheRedisTCP(string $host = "redis", int $port = 6379, int $timeout = 3): void
    {
        if ($this->dockerConfigLocked == true) {
            return;
        }
        $this->use_redis_cache = true;
        $this->redisUnix = false;
        $this->redis_host = $host;
        $this->redis_port = $port;
        $this->redis_timeout = $timeout;
    }
    public function configCacheDisk(string $folder = "cache"): void
    {
        if ($this->dockerConfigLocked == true) {
            return;
        }
        $this->use_disk_cache = true;
        $this->disk_cache_folder = $folder;
    }

    public function setupCache(): void
    {
        $this->Cache = null;
        if ($this->use_redis_cache == true) {
            $this->startRedisCache();
        } elseif ($this->use_disk_cache == true) {
            $this->startDiskCache();
        }
        return;
    }

    /*
        Tables to enable with cache
    */
    protected function setupCacheTables(): void
    {
    }

    public function startCache(): void
    {
        $this->setupCacheTables();
        if ($this->use_redis_cache == true) {
            $this->Cache->start(false);
        } elseif ($this->use_disk_cache == true) {
            $this->Cache->start(true);
        }
        return;
    }

    protected function startRedisCache(): void
    {
        $this->Cache = new Redis();
        if ($this->redisUnix == true) {
            $this->Cache->connectUnix($this->redis_socket);
            return;
        }
        $this->Cache->setTimeout($this->redis_timeout);
        $this->Cache->connectTCP($this->redis_host, $this->redis_port);
    }

    protected function startDiskCache(): void
    {
        $this->Cache = new Disk($this->disk_cache_folder);
    }
}
