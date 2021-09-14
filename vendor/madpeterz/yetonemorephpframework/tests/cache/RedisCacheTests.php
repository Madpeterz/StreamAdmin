<?php

namespace YAPF\Junk;

use PHPUnit\Framework\TestCase;
use YAPF\Cache\Cache;
use YAPF\Cache\Drivers\Redis;
use YAPF\Junk\Models\Counttoonehundo;
use YAPF\Junk\Models\Liketests;
use YAPF\Junk\Sets\CounttoonehundoSet;
use YAPF\Junk\Sets\LiketestsSet;
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
            time() + 1
        );
        $cache->shutdown();
        sleep(2);
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, true);
        $cache->start();

        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero as the cache was used");
        $countto = new CounttoonehundoSet();
        $countto->attachCache($cache);
        $countto->loadNewest(1);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one as the cache should be expired");
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
     **/
    public function testSingles(): void
    {
        global $sql;
        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero");
        $countto = new Counttoonehundo();
        $cache = $this->getCache();
        $cache->purge();
        $cache->addTableToCache($countto->getTable(), 10, false,true);
        $cache->start();
        $countto->attachCache($cache);
        $countto->loadID(44);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one");
        $cache->shutdown();

        $countto = new Counttoonehundo();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, false,true);
        $cache->start();
        $countto->attachCache($cache);
        $countto->loadID(44);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one");
        $cache->shutdown();
    }

    /**
     * @depends testSingles
     */
    public function testSinglesAccountHash(): void
    {
        global $sql;
        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero");
        $countto = new Counttoonehundo();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, false,true);
        $cache->start();
        $cache->setAccountHash("magic");
        $countto->attachCache($cache);
        $countto->loadID(44);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one");
        $cache->shutdown();

        $countto = new Counttoonehundo();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, false,true);
        $cache->start();
        $cache->setAccountHash("magic");
        $countto->attachCache($cache);
        $countto->loadID(44);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one");
        $cache->shutdown();
    }

    /**
     * @depends testSinglesAccountHash
     */
    public function testSingleCacheHitButChanged(): void
    {
        global $sql;
        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero");
        $countto = new Counttoonehundo();
        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, false,true);
        $cache->start();
        $cache->setAccountHash("magic");
        $countto->attachCache($cache);
        $countto->loadID(44);
        $this->assertSame(0, $sql->getSQLselectsCount(), "DB reads should be zero (cache should be hit)");
        $countto->setCvalue($countto->getCvalue()+1);
        $countto->updateEntry();
        $countto = new Counttoonehundo();
        $countto->attachCache($cache);
        $countto->loadID(44);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one due to cache miss due to change live");
        $cache->shutdown();

        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, false,true);
        $cache->start();
        $cache->setAccountHash("magic");
        $countto->attachCache($cache);
        $countto->loadID(44);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one from the updated cache entry");
        $countto = new Counttoonehundo();
        $countto->attachCache($cache);
        $countto->loadID(11);
        $this->assertSame(2, $sql->getSQLselectsCount(), "DB reads should be two from the read");
        $countto->setCvalue($countto->getCvalue()+1);
        $countto->updateEntry();
        $cache->shutdown();

        $cache = $this->getCache();
        $cache->addTableToCache($countto->getTable(), 10, false,true);
        $cache->start();
        $cache->setAccountHash("magic");
        $countto->attachCache($cache);
        $countto->loadID(44);
        $this->assertSame(3, $sql->getSQLselectsCount(), "DB reads should be three due to the cache table having changes");
    }


    /**
     * @depends testSingleCacheHitButChanged
     */
    public function testCatcheNotThere(): void
    {
        global $sql;
        $cache = new Redis();
        $cache->connectTCP("127.0.0.1",7777);
        $countto = new Counttoonehundo();
        $countto->attachCache($cache);
        $countto->loadID(11);
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one from the failed read");
        $this->assertSame(false,$cache->getStatusConnected(),"Cache should not be connected");
    }

    /**
     * @depends testSingleCacheHitButChanged
     */
    public function testLimitedMode()
    {
        global $sql;
        $cache = $this->getCache();
        $LiketestsSet = new LiketestsSet();
        $cache->addTableToCache($LiketestsSet->getTable(), 15, false, true);
        $cache->start();
        $LiketestsSet->limitFields(["name"]);
        $LiketestsSet->attachCache($cache);
        $LiketestsSet->loadAll();
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one due to cache miss");
        $cache->shutdown();
        $reply = $LiketestsSet->updateFieldInCollection("value","failme");
        $this->assertSame(false,$reply["status"],"bulk set value incorrectly");

        $cache = $this->getCache();
        $LiketestsSet = new LiketestsSet();
        $cache->addTableToCache($LiketestsSet->getTable(), 15, false, true);
        $cache->start();
        $LiketestsSet->limitFields(["name"]);
        $LiketestsSet->attachCache($cache);
        $LiketestsSet->loadAll();
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should still be one due to the hit");
        $cache->shutdown();
        $reply = $LiketestsSet->updateFieldInCollection("value","failme");
        $this->assertSame(false,$reply["status"],"bulk set value incorrectly");

        $obj = $LiketestsSet->getObjectByID(1);
        $this->assertSame("redpondblue 1",$obj->getName(),"Value is not set as expected");
        $this->assertSame(null,$obj->getValue(),"Value is not what is expected");


        $cache = $this->getCache();
        $testing = new Liketests();
        $cache->addTableToCache($testing->getTable(), 15, false, true);
        $cache->start();
        $testing->attachCache($cache);
        $testing->limitFields(["name"]);
        $this->assertSame(true,$testing->getUpdatesStatus(),"Set should be marked as update disabled");
        $testing->loadID(1);
        $sqlExpected = "SELECT id, name FROM test.liketests  WHERE `id` = ?";
        $this->assertSame($sqlExpected,$testing->getLastSql(),"SQL is not what was expected");
        $this->assertSame("redpondblue 1",$testing->getName(),"Value is not set as expected");
        $this->assertSame(null,$testing->getValue(),"Value is not what is expected");
        $this->assertSame(2, $sql->getSQLselectsCount(), "DB reads should be two due to the miss");
        $cache->shutdown();


        $cache = $this->getCache();
        $testing = new Liketests();
        $cache->addTableToCache($testing->getTable(), 15, false, true);
        $cache->start();
        $testing->attachCache($cache);
        $testing->limitFields(["name"]);
        $this->assertSame(true,$testing->getUpdatesStatus(),"Single should be marked as update disabled");
        $testing->loadID(1);
        $sqlExpected = "SELECT id, name FROM test.liketests  WHERE `id` = ?";
        $this->assertSame($sqlExpected,$testing->getLastSql(),"SQL is not what was expected");
        $this->assertSame("redpondblue 1",$testing->getName(),"Value is not set as expected");
        $this->assertSame(null,$testing->getValue(),"Value is not what is expected");
        $this->assertSame(2, $sql->getSQLselectsCount(), "DB reads should still be two due to the hit");
        $cache->shutdown();
    }

    /**
     * @depends testLimitedMode
     */
    public function testCountinDb()
    {
        global $sql;
        $cache = $this->getCache();
        $testing = new LiketestsSet();
        $cache->addTableToCache($testing->getTable(), 15, false, true);
        $cache->start();
        $testing->attachCache($cache);
        $reply = $testing->countInDB();
        $expectedSQL = "SELECT COUNT(id) AS sqlCount FROM test.liketests";
        $this->assertSame($expectedSQL,$sql->getLastSql(),"SQL is not what was expected");
        $this->assertSame(4,$reply,"incorrect count reply");
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should be one due to the miss");
        $cache->shutdown();

        $cache = $this->getCache();
        $testing = new LiketestsSet();
        $cache->addTableToCache($testing->getTable(), 15, false, true);
        $cache->start();
        $testing->attachCache($cache);
        $reply = $testing->countInDB();
        $expectedSQL = "SELECT COUNT(id) AS sqlCount FROM test.liketests";
        $this->assertSame($expectedSQL,$sql->getLastSql(),"SQL is not what was expected");
        $this->assertSame(4,$reply,"incorrect count reply");
        $this->assertSame(1, $sql->getSQLselectsCount(), "DB reads should still be one due to the hit");
        $cache->shutdown();
    }


    protected function getCacheHashId(Cache $cache): string
    {
        $where_config = [
            "join_with" => "AND",
             "fields" => [],
             "matches" => [],
             "values" => [],
             "types" => [],
        ];
        $basic_config = ["table" => "test.counttoonehundo"];
        $order_config = ["ordering_enabled" => true,"order_field" => "id","order_dir" => "DESC"];
        $limit_config = ["page_number" => 0,"max_entrys" => 1];
        return $cache->getHash(
            $where_config,
            $order_config,
            $limit_config,
            $basic_config,
            "test.counttoonehundo",
            2
        );
    }
}
