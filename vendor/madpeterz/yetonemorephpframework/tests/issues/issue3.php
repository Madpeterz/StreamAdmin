<?php

namespace YAPF\Junk;

use PHPUnit\Framework\TestCase;
use YAPF\Junk\Models\Counttoonehundo;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;


$sql = null;
class Issue3Test extends TestCase
{
    /* @var YAPF\MySQLi\MysqliEnabled $sql */
    protected $sql = null;
    protected function setUp(): void
    {
        global $sql;
        $sql = new MysqliConnector();
    }
    protected function tearDown(): void
    {
        global $sql;
        $sql->sqlSave(true);
        $sql = null;
    }

    public function testIssue3()
    {
        global $sql;
        $countto = new Counttoonehundo();
        $load_status = $countto->loadID(44);
        $this->assertSame($load_status, true);
        $this->assertSame($countto->getId(), 44);
        $this->assertSame($countto->getCvalue(), 10);
        $mapped_array = $countto->objectToMappedArray();
        $this->assertSame(2,count($mapped_array),"Cvalue and id should both be in the array");
        $mapped_array = $countto->objectToMappedArray(["id"]);
        $this->assertSame(1,count($mapped_array),"only Cvlaue should be found in the array");
    }
}
