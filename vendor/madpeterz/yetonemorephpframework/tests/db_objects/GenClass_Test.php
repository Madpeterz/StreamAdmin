<?php

namespace YAPF\Junk;

use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;
use YAPF\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Junk\Models\Alltypestable;
use YAPF\Junk\Models\Counttoonehundo;

// Do not edit this file, rerun gen.php to update!
class BrokenDbObject extends genClass
{
    protected $use_table = "test.counttoonehundo";
    protected $fields = ["id","cvalue"];
    protected $dataset = [
        "cvalue" => ["type" => "int", "value" => null],
    ];
    public function getCvalue(): ?int
    {
        return $this->getField("cvalue");
    }
    /**
    * setCvalue
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setCvalue(?int $newvalue): array
    {
        return $this->updateField("cvalue", $newvalue);
    }
    public function getMissingIndex(): ?string
    {
        return $this->getField("missing");
    }
}

class VeryBrokenDbObject extends genClass
{
    protected $use_table = "test.counttoonehundo";
    protected $fields = ["id","cvalue"];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "cvalue" => ["type" => "int", "value" => null],
    ];
    public function getCvalue(): ?int
    {
        return $this->getField("cvalue");
    }
    /**
    * setCvalue
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setCvalue(?int $newvalue): array
    {
        $this->updateField("cvalue", $newvalue);
        $this->save_dataset = [];
        $this->save_dataset["id"] = ["type" => "int", "value" => 99];
        return ["status" => true,"message" => "ok"];
    }
}
class VeryBrokenDbObjectNoId extends genClass
{
    protected $use_table = "test.counttoonehundo";
    protected $fields = ["id","cvalue"];
    protected $dataset = [
        "cvalue" => ["type" => "int", "value" => null],
    ];
}
class WeirdBrokenObjectWithSaveDatasetButNoLive extends GenClass
{
    protected $use_table = "test.counttoonehundo";
    protected $fields = ["id","cvalue"];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "cvalue" => ["type" => "int", "value" => null],
    ];
    public function getCvalue(): ?int
    {
        $this->dataset = [];
        return 1;
    }
}
class WeirdBrokenObjectWithSaveDatasetButMalformedLive extends GenClass
{
    protected $use_table = "test.counttoonehundo";
    protected $fields = ["id","cvalue"];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "cvalue" => ["type" => "int", "value" => null],
    ];
    public function getCvalue(): ?int
    {
        $this->dataset["cvalue"] = ["type" => "int"];
        return 1;
    }
}


$sql = new MysqliConnector();
class DbObjectsGenClassTest extends TestCase
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
    public function testResetDbFirst()
    {
        global $sql;
        $results = $sql->rawSQL("tests/testdataset.sql");
        // [status =>  bool, message =>  string]
        $this->assertSame($results["status"], true);
        $this->assertSame($results["message"], "56 commands run");
    }
    public function testHasAny()
    {
        global $sql;
        $testing = new Alltypestable();
        $this->assertSame($testing->hasAny(), false);
        $testing = new Counttoonehundo();
        $this->assertSame($testing->hasAny(), true);
    }
    public function testDisabled()
    {
        $countto = new Counttoonehundo();
        $result = $countto->setCvalue(22);
        $countto->makedisabled();
        $result = $countto->setCvalue(823);
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "This class is disabled");
        $result = $countto->createEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "this class is disabled.");
        $countto = new Counttoonehundo();
        $countto->loadID(44);
        $countto->makedisabled();
        $result = $countto->removeEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "this class is disabled.");
        $result = $countto->updateEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "this class is disabled.");
    }

    public function testCreateDefaultIdDetected()
    {
        $countto = new Counttoonehundo(["id" => 44]);
        $result = $countto->createEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "attempting to create a object with a set id, this is not allowed!");
    }

    public function testCreateBrokenObject()
    {
        $broken = new BrokenDbObject();
        $broken->setCvalue(44);
        $result = $broken->createEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "id field is required on the class to support create");
    }

    public function testUpdateMissingId()
    {
        $broken = new Counttoonehundo(["cvalue" => 44]);
        $result = $broken->updateEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "Object id is not vaild for updates");
    }

    public function testUpdateInvaildIdDetected()
    {
        $countto = new Counttoonehundo(["id" => -88,"cvalue" => 44]);
        $result = $countto->updateEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "Object id is not vaild for updates");
    }

    public function testUpdateVeryBrokenObject()
    {
        $verybroken = new VeryBrokenDbObject(["id" => 44,"cvalue" => 55]);
        $verybroken->setCvalue(44);
        $result = $verybroken->updateEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "No changes made");
    }

    public function testUpdateVeryBrokenObjectNoId()
    {
        $verybroken = new VeryBrokenDbObjectNoId(["cvalue" => 55]);
        $result = $verybroken->updateEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "Object does not have its id field set!");
    }
    public function testWeirdBrokenObject()
    {
        $weird = new WeirdBrokenObjectWithSaveDatasetButNoLive(["cvalue" => 44,"id" => 44]);
        $weird->getCvalue();
        $result = $weird->updateEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "request rejected: Key: cvalue is missing from dataset!");
    }
    public function testWeirdBrokenObjectAgain()
    {
        $weird = new WeirdBrokenObjectWithSaveDatasetButMalformedLive(["cvalue" => 44,"id" => 44]);
        $weird->getCvalue();
        $result = $weird->updateEntry();
        $this->assertSame($result["status"], false);
        $this->assertSame($result["message"], "request rejected: Key: cvalue is missing its value index!");
    }
    public function testCreateUID()
    {
        $testing = new Alltypestable();
        $testing->setFloatfield(44.1);
        $testing->setIntfield(11);
        $result = $testing->createUID("stringfield", 5);
        $this->assertSame($result["message"], "ok");
        $this->assertSame($result["status"], true);
        $this->assertSame(strlen($result["uid"]), 5);
    }
    public function testGetHash()
    {
        $countto = new Counttoonehundo();
        $countto->loadID(44);
        $this->assertSame($countto->fieldsHash(), "2c624232cdd221771294dfbb310aca000a0df6ac8b66b696d90ef06fdefb64a3");
    }
    public function testObjectToMappedArray()
    {
        $countto = new Counttoonehundo();
        $countto->loadID(44);
        $mapped = $countto->objectToMappedArray();
        $this->assertSame($mapped["id"], 44);
    }
    public function testObjectToValueArray()
    {
        $countto = new Counttoonehundo();
        $countto->loadID(44);
        $mapped = $countto->objectToValueArray();
        $this->assertSame(in_array(44, $mapped), true);
    }
    public function testObjectHasField()
    {
        $countto = new Counttoonehundo();
        $countto->loadID(44);
        $this->assertSame($countto->hasField("fake"), false);
        $this->assertSame($countto->hasField("cvalue"), true);
    }
    public function testGetFields()
    {
        $countto = new Counttoonehundo();
        $countto->loadID(44);
        $fields = $countto->getFields();
        $this->assertSame(count($fields), 2);
        $this->assertSame(true, in_array("cvalue",$fields));
    }
    public function testIsLoadedOnNonLoadedObject()
    {
        $countto = new Counttoonehundo();
        $this->assertSame($countto->isLoaded(), false);
    }
    public function testGetAllTypes()
    {
        $alltypes = new Alltypestable();
        $alltypes->setIntfield(11);
        $alltypes->setFloatfield(124.55);
        $alltypes->setStringfield("Hello world");
        $alltypes->setBoolfield(true);
        $this->assertSame($alltypes->getIntfield(), 11);
        $this->assertSame($alltypes->getFloatfield(), 124.55);
        $this->assertSame($alltypes->getStringfield(), "Hello world");
        $this->assertSame($alltypes->getBoolfield(), true);
        $BrokenDbObject = new BrokenDbObject();
        $result = $BrokenDbObject->getMissingIndex();
        $this->assertSame($result, null);
        $expected_error = "YAPF\Junk\BrokenDbObject Attempting to get field that does not exist";
        $this->assertSame($BrokenDbObject->getLastErrorBasic(), $expected_error);
    }
    public function testGetFieldType()
    {
        $alltypes = new Alltypestable();
        $this->assertSame($alltypes->getFieldType("floatfield", false), "float");
        $this->assertSame($alltypes->getFieldType("floatfield", true), "d");
        $this->assertSame($alltypes->getFieldType("intfield", false), "int");
        $this->assertSame($alltypes->getFieldType("intfield", true), "i");
        $this->assertSame($alltypes->getFieldType("stringfield", false), "str");
        $this->assertSame($alltypes->getFieldType("stringfield", true), "s");
        $this->assertSame($alltypes->getFieldType("boolfield", false), "bool");
        $this->assertSame($alltypes->getFieldType("boolfield", true), "i");
        $this->assertSame($alltypes->getFieldType("fake"), null);
        $expected_error = "YAPF\Junk\Models\Alltypestable Attempting to read a fieldtype [fake] has failed";
        $this->assertSame($alltypes->getLastErrorBasic(), $expected_error);
    }
}
