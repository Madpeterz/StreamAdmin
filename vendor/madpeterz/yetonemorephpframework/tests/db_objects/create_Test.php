<?php

namespace YAPF\Junk;

use PHPUnit\Framework\TestCase;
use YAPF\Junk\Models\Alltypestable;
use YAPF\Junk\Models\Endoftestwithfourentrys;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

$sql = null;
class DbObjectsCreateTest extends TestCase
{
    /* @var YAPF\MySQLi\MysqliEnabled $sql */
    protected $sql = null;
    protected function setUp(): void
    {
        global $sql;
        define("REQUIRE_ID_ON_LOAD", true);
        $sql = new MysqliConnector();
    }
    protected function tearDown(): void
    {
        global $sql;
        $sql->sqlSave(true);
        $sql = null;
    }
    public function testCreate()
    {
        global $sql;
        $testing = new Alltypestable();
        $result = $testing->setStringfield("magic");
        $this->assertSame($result["status"], true);
        $result = $testing->setIntfield(44);
        $this->assertSame($result["status"], true);
        $result = $testing->setFloatfield(2.5);
        $this->assertSame($result["status"], true);
        $result = $testing->createEntry();
        // newID => ?int, rowsAdded => int, status => bool, message => string
        $this->assertSame($result["status"], true);
        $this->assertSame($result["message"], "ok");
        $this->assertSame($testing->getId(), 2);
    }

    public function testCreateInvaild()
    {
        $testing = new Endoftestwithfourentrys();
        $result = $testing->createEntry();
        $this->assertSame($result["message"], "unable to execute because: Column 'value' cannot be null");
        $this->assertSame($result["status"], false);
        $this->assertSame($testing->getId(), null);
    }

    public function testCreateThenUpdate()
    {
        $testing = new Endoftestwithfourentrys();
        $result = $testing->setValue("woof");
        $this->assertSame($result["status"], true);
        $result = $testing->createEntry();
        // newID => ?int, rowsAdded => int, status => bool, message => string
        $this->assertSame($result["status"], true);
        $this->assertSame($result["message"], "ok");
        $this->assertSame($testing->getId(), 1);
        $testing->setValue("moo");
        $result = $testing->updateEntry();
        $this->assertSame($result["status"], true);
    }
}
