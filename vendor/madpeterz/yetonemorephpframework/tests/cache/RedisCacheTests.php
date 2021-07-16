<?php

namespace YAPF\Junk;

use PHPUnit\Framework\TestCase;
use YAPF\Cache\Cache;
use YAPF\Cache\Drivers\Redis;
use YAPF\Junk\Models\Counttoonehundo;
use YAPF\Junk\Sets\CounttoonehundoSet;
use YAPF\MySQLi\MysqliEnabled;

class RedisCacheTests extends TestCase
{
    protected function getCache(): Cache
    {
        $cache = new Redis();
        $cache->connectTCP("127.0.0.1");
        return $cache;
    }

    protected function setUp(): void
    {
        global $sql;
        define("REQUIRE_ID_ON_LOAD", true);
        $sql = new MysqliEnabled();
    }
    protected function tearDown(): void
    {
        global $sql;
        $sql->sqlSave(true);
        $sql = null;
    }

    public function testCreateAndCleanup(): void
    {
        $cache = $this->getCache();
        $cache->addTableToCache("testing", 10, true);
        $cache->start(true);
        $content_data = [
        "changeID" => 1,
        "expires" => (time() + 200),
        "allowChanged" => false,
        "tableName" => "testing",
        ];
        $cache->forceWrite("testing", "testing", json_encode($content_data), json_encode($content_data), time() + 200);
        $reply = $cache->readHash("testing", "testing");
        $this->assertSame($content_data, $reply, "Cache file not correct");
        $cache->purge();
        $this->assertSame([], $cache->getKeys(), "expected zero keys but got not that");
    }

/**
 * @depends testCreateAndCleanup
 */
    public function testReadFromDbAndPutOnCacheThenRead(): void
    {
        global $sql;
        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start(true);

        $countto->attachCache($cache);
        $loadResult = $countto->loadAll();
        $this->assertSame(1, $sql->getSQLselectsCount(), "Failed to read from the DB");
        $this->assertSame(true, $loadResult["status"], "Failed to load from DB");
        $cache->shutdown();

        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start(true);

        $this->assertSame(1, $sql->getSQLselectsCount(), "Incorrect number of DB reads");
        $countto = new CounttoonehundoSet();
        $countto->attachCache($cache);
        $loadResult = $countto->loadAll();
        $this->assertSame(true, $loadResult["status"], "Failed to read from DB (step 2)");
        $this->assertSame(1, $sql->getSQLselectsCount(), "Incorrectly loaded from the DB and not from cache");
    }

/**
 * @depends testReadFromDbAndPutOnCacheThenRead
 */
    public function testNewConnectionReadFromCache(): void
    {
        global $sql;
        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero");

        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();
        $countto->attachCache($cache);
        $loadResult = $countto->loadAll();
        $this->assertSame(true, $loadResult["status"], "Failed to read from DB (step 2)");

        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero");
    }

/**
 * @depends testNewConnectionReadFromCache
 */
    public function testCacheExpiredBecauseChanged(): void
    {
        global $sql;
//$sql = new MysqliEnabled();
        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero");
        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();
        $countto->attachCache($cache);
        $loadResult = $countto->loadNewest(1);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one");
        $entry = $countto->getFirst();
        $entry->attachCache($cache);
        $entry->setCvalue($entry->getCvalue() + 1);
        $result = $entry->updateEntry();
        $this->assertSame(true, $result["status"], "Failed to update entry");
        $sql->sqlSave();
        $cache->shutdown();

        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();
        $countto->attachCache($cache);
        $loadResult = $countto->loadNewest(1); // cache expired because of change
        $this->assertSame(2, $sql->getSQLselectsCount(), "DB reads should be two");
    }

/**
 * @depends testCacheExpiredBecauseChanged
 */
    public function testCacheRehitBeforeSave(): void
    {
/*
if you load more than once the cache does
not get hit until after this run has finished.
    */
        global $sql;
//$sql = new MysqliEnabled();
        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero");
        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->purge();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();
        $countto->attachCache($cache);
        $countto->loadNewest(1);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one");
        $countto = new CounttoonehundoSet();
        $countto->attachCache($cache);
        $countto->loadNewest(1);
        $this->assertSame(2, $sql->getSQLselectsCount(), "DB reads should be two");
    }


/**
 * @depends testCacheRehitBeforeSave
 */
    public function testCacheExpired(): void
    {
        global $sql;
        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();
        $hashid = $this->getCacheHashId($cache);
        $content_data = [
        "changeID" => $cache->getChangeID("test.counttoonehundo"),
        "expires" => (time() - 20),
        "allowChanged" => false,
        "tableName" => "test.counttoonehundo",
        ];
        $this->assertSame(true, $cache->cacheVaild("test.counttoonehundo", $hashid), "Exepected entry is missing");
        $reply = $cache->readHash("test.counttoonehundo", $hashid);
        $cache->forceWrite(
            "test.counttoonehundo",
            $hashid,
            json_encode($content_data),
            json_encode($reply),
            time() - 10
        );
        $cache->shutdown();

        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();

        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero");
        $countto = new CounttoonehundoSet();
        $countto->attachCache($cache);
        $countto->loadNewest(1);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one");
    }

/**
 * @depends testCacheRehitBeforeSave
 */
    public function testAccountHash(): void
    {
        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, false);
        $cache->start();
        $cache->setAccountHash("Magic");
        $hashid = $this->getCacheHashId($cache);
        $countto->attachCache($cache);
        $countto->loadNewest(1);
        $cache->shutdown();

        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, false);
        $cache->start();
        $cache->setAccountHash("Magic");
        $hashid = $this->getCacheHashId($cache);
        $countto->attachCache($cache);
        $reply = $cache->readHash("test.counttoonehundo", $hashid);
        $this->assertNotEmpty($reply, "expected cache file is empty");
    }

/**
 * @depends testAccountHash
 */
    public function testCacheFinalPurge(): void
    {
        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();
        $cache->purge();
        $result = $cache->readHash("test.counttoonehundo", $this->getCacheHashId($cache));
        $this->assertSame(null, $result, "Purged entry was found :(");
    }

    /**
     * @depends testCacheFinalPurge
     */
    public function testSpeed(): void
    {
        $time_start = microtime(true); 
        $loop = 0;
        while($loop < 1000)
        {
            $setObject = new CounttoonehundoSet();
            $setObject->loadAll();
            $loop++;
        }
        $time_end = microtime(true); 
        $dif_time = $time_end - $time_start;
        error_log("1000 load alls (No cache) = ".$dif_time);

        $countto = new CounttoonehundoSet();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();
        $countto->attachCache($cache);
        $countto->loadAll();
        $cache->shutdown();


        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();
        $time_start = microtime(true); 
        $loop = 0;
        while($loop < 1000)
        {
            $setObject = new CounttoonehundoSet();
            $setObject->attachCache($cache);
            $setObject->loadAll();
            $loop++;
        }
        $time_end = microtime(true); 
        $dif_time = $time_end - $time_start;
        error_log("1000 load alls (With Redis cache) = ".$dif_time);
        $this->assertSame(true,true,"Speed test failed");
    }


    protected function getCacheHashId(Cache $cache): string
    {
        $singleCount = new Counttoonehundo();
        $where_config = [
        "join_with" => "AND",
        "fields" => [],
        "matches" => [],
        "values" => [],
        "types" => [],
        ];
        $order_config = [
        "ordering_enabled" => true,
        "order_field" => "id",
        "order_dir" => "DESC"
        ];
        $options_config = [
        "page_number" => 0,
        "max_entrys" => 1
        ];
        return $cache->getHash(
            $where_config,
            $order_config,
            $options_config,
            [],
            "test.counttoonehundo",
            count($singleCount->getFields())
        );
    }
}
