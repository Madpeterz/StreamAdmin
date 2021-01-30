<?php

namespace YAPF\Junk;

use PHPUnit\Framework\TestCase;
use YAPF\Junk\Models\Alltypestable;
use YAPF\Junk\Models\Endoftestempty;
use YAPF\Junk\Sets\LiketestsSet;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

$sql = null;
class DbObjectsUpdateTest extends TestCase
{
    /* @var YAPF\MySQLi\MysqliEnabled $sql */
    protected function setUp(): void
    {
        global $sql;
        if (defined("REQUIRE_ID_ON_LOAD") == false) {
            define("REQUIRE_ID_ON_LOAD", true);
        }
        $sql = new MysqliConnector();
    }
    protected function tearDown(): void
    {
        global $sql;
        $sql->sqlSave(true);
        $sql = null;
    }

    public function testUpdateSingle()
    {
        global $sql;
        $target = new Endoftestempty();
        $result = $target->loadByField("name", "yes");
        $this->assertSame($result, true);
        $result = $target->setName("Magic");
        $this->assertSame($result["status"], true);
        $result = $target->updateEntry();
        $this->assertSame($result["status"], true);
        $this->tearDown();
        $this->assertSame($sql, null); // reset mysql connection
        $this->setUp();
        $target = new Endoftestempty();
        $result = $target->loadID(1);
        $this->assertSame($result, true);
        $this->assertSame($target->getName(), "Magic");
    }

    public function testUpdateSet()
    {
        $target = new LiketestsSet();
        $result = $target->loadAll();
        $this->assertSame($result["status"], true);
        $this->assertSame($result["count"], 4);
        $result = $target->updateFieldInCollection("value", "Song");
        $this->assertSame($result["status"], true);
        $this->assertSame($result["changes"], 3);
    }

    public function testUpdateSetInvaild()
    {
        $target = new LiketestsSet();
        $result = $target->loadAll();
        $this->assertSame($result["status"], true);
        $this->assertSame($result["count"], 4);
        $result = $target->updateFieldInCollection("value", null);
        $this->assertSame($result["status"], false);
        $this->assertSame($result["changes"], 0);
        $fail_message = "Update failed because:unable to execute because: Column 'value' cannot be null";
        $this->assertSame($result["message"], $fail_message);
    }

    public function testUpdateSetEmpty()
    {
        $target = new LiketestsSet();
        $result = $target->updateFieldInCollection("value", "yes");
        $this->assertSame($result["status"], false);
        $this->assertSame($result["changes"], 0);
        $fail_message = "Nothing loaded in collection";
        $this->assertSame($result["message"], $fail_message);
    }

    public function testUpdateFloat()
    {
        $target = new Alltypestable();
        $target->setFloatfield(23.4);
        $target->setIntfield(55);
        $target->setStringfield("Hello world");
        $target->createEntry();
        $target->setFloatfield(55.81);
        $result = $target->updateEntry();
        $this->assertSame($result["status"], true);
        $this->assertSame($result["changes"], 1);
    }
}
