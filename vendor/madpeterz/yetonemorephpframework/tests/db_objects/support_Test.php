<?php

namespace YAPF\Junk;

use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;
use YAPF\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Junk\Models\Counttoonehundo;
use YAPF\Junk\Models\Liketests;

$sql = null;

class BrokenObjectThatSetsWhatever extends genClass
{
    protected $use_table = "test.counttoonehundo";
    protected $fields = ["id","cvalue"];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "cvalue" => ["type" => "int", "value" => null],
    ];
    /**
    * setCvalue
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setCvalue($newvalue, string $fieldname = "cvalue"): array
    {
        return $this->updateField($fieldname, $newvalue);
    }
}
class DbObjectsSupportTest extends TestCase
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
        if ($sql != null) {
            $sql->sqlSave(true);
        }
        $sql = null;
    }
    public function testLastSql()
    {
        global $sql;
        $testing = new Liketests();
        $this->assertSame($testing->getLastSql(), "");
        $testing->loadID(1);
        $this->assertSame($testing->getLastSql(), 'SELECT * FROM test.liketests  WHERE `id` = ?');
    }
    public function testLastSQlWithNullGlobalSql()
    {
        global $sql;
        $sql = null;
        $testing = new liketests();
        $this->assertSame($testing->getLastSql(), "");
    }
    public function testPassSetupInvaildFields()
    {
        $testing = new Counttoonehundo();
        $result = $testing->setup(["fake" => true]);
        $this->assertSame($result, true); // invaild fields are ignored
    }
    public function testSetTable()
    {
        $testing = new Counttoonehundo();
        $testing->setTable("wrongtable");
        $this->assertSame($testing->getTable(), "wrongtable");
    }
    public function testSetId()
    {
        $testing = new Counttoonehundo();
        $result = $testing->setId(44);
        $this->assertSame($result["message"], "value set");
        $this->assertSame($result["status"], true);
        $this->assertSame($testing->getId(), 44);
    }
    public function testSetIdWithBadIdSet()
    {
        $testing = new Counttoonehundo();
        $testing->setBadId();
        $result = $testing->setId(44);
        $this->assertSame($result["message"], "bad_id flag is set unable to setId");
        $this->assertSame($result["status"], false);
        $this->assertSame($testing->getId(), null);
    }
    public function testSetWeirdness()
    {
        $target = new BrokenObjectThatSetsWhatever();
        $result = $target->setCvalue(new BrokenObjectThatSetsWhatever());
        $this->assertSame($result["message"], "System error: Attempt to put a object onto field: cvalue");
        $this->assertSame($result["status"], false);
        $result = $target->setCvalue([123,1234,12341]);
        $this->assertSame($result["message"], "System error: Attempt to put a array onto field: cvalue");
        $this->assertSame($result["status"], false);
        $result = $target->setCvalue("woof", "dognoise");
        $this->assertSame($result["message"], "Sorry this object does not have the field: dognoise");
        $this->assertSame($result["status"], false);
        $result = $target->setCvalue(33, "id");
        $this->assertSame($result["message"], "Sorry this object does not allow you to set the id field!");
        $this->assertSame($result["status"], false);
        $target->disableAllowSetField();
        $result = $target->setCvalue(1234);
        $this->assertSame($result["message"], "update_field is not allowed for this object");
        $this->assertSame($result["status"], false);
    }
}
