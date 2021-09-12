<?php

namespace App;

use YAPF\Cache\Drivers\Disk;
use YAPF\Cache\Drivers\Redis;

$use_redis_cache = false;
$use_disk_cache = false;

$disk_cache_folder = ROOTFOLDER . "/cache";

$redis_tcp = true;
$redis_host = "redis";
$redis_port = 6379;
$redis_timeout = 3;

$redis_socket = "/var/run/redis/redis.sock";

// Please do not edit below this line.
$cache = null;
if (getenv('SITE_CACHE_ENABLED') !== false) {
    $use_disk_cache = false;
    $use_redis_cache = true;
    $redis_host = getenv("SITE_CACHE_REDIS_HOST");
}

if ($use_disk_cache == true) {
    $cache = new Disk($disk_cache_folder);
} elseif ($use_redis_cache == true) {
    $cache = new Redis();
    if ($redis_tcp == true) {
        $cache->setTimeout($redis_timeout);
        $cache->connectTCP($redis_host, $redis_port);
    } else {
        $cache->connectUnix($redis_socket);
    }
}

if ($cache != null) {
    $cache->addTableToCache("apis", 120, true, true);
    $cache->addTableToCache("banlist", 120, true);
    $cache->addTableToCache("botconfig", 120, true);
    $cache->addTableToCache("noticenotecard", 120, true);
    $cache->addTableToCache("notice", 120, true, true);
    $cache->addTableToCache("package", 120, true, true);
    $cache->addTableToCache("region", 120, true);
    $cache->addTableToCache("reseller", 120, true, true);
    $cache->addTableToCache("server", 120, true, true);
    $cache->addTableToCache("servertypes", 120, true);
    $cache->addTableToCache("slconfig", 120, true, true);
    $cache->addTableToCache("staff", 120, true, true);
    $cache->addTableToCache("template", 120, true, true);
    $cache->addTableToCache("textureconfig", 120, true, true);
    $cache->addTableToCache("timezones", 120, true, true);
    $cache->addTableToCache("treevender", 120, true);
    $cache->addTableToCache("treevenderpackages", 120, true);
    $cache->start(true);
}
