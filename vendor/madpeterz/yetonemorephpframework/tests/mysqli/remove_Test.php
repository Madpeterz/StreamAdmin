<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

class mysqli_remove_test extends TestCase
{
    protected ?MysqliConnector $sql;
    protected function setUp(): void
    {
        $this->sql = new MysqliConnector();
    }
    protected function tearDown(): void
    {
        $this->sql->sqlSave(true);
        $this->sql = null;
    }
    public function testRestoreDbFirst()
    {
        $results = $this->sql->rawSQL("tests/testdataset.sql");
        // [status =>  bool, message =>  string]
        $this->assertSame($results["status"], true);
        $this->assertSame($results["message"], "56 commands run");
    }

    public function testRemove()
    {
        $where_config = [
        "fields" => ["id"],
        "values" => [2],
        "matches" => ["="],
        "types" => ["i"]
        ];
        $results = $this->sql->removeV2("endoftestempty", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["status"], true);
        $this->assertSame($results["rowsDeleted"], 1);
        $this->assertSame($results["message"], "ok");
    }

    public function testRemoveInvaildTable()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["i"]
        ];
        $results = $this->sql->removeV2("badtable", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["rowsDeleted"], 0);
        $error_msg = "unable to prepair: Table 'test.badtable' doesn't exist";
        $this->assertSame($results["message"], $error_msg);
        $results = $this->sql->removeV2("", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["rowsDeleted"], 0);
        $this->assertSame($results["message"], "No table given");
    }

    public function testRemoveInvaildField()
    {
        $where_config = [
        "fields" => ["badtheif"],
        "values" => [1],
        "matches" => ["="],
        "types" => ["i"]
        ];
        $results = $this->sql->removeV2("endoftestempty", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["rowsDeleted"], 0);
        $error_msg = "unable to prepair: Unknown column 'badtheif' in 'where clause'";
        $this->assertSame($results["message"], $error_msg);
    }

    public function testRemoveInvaildValue()
    {
        $where_config = [
        "fields" => ["id"],
        "values" => ["1"],
        "matches" => ["="],
        "types" => ["s"]
        ];
        $results = $this->sql->removeV2("endoftestempty", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["status"], true);
        $this->assertSame($results["rowsDeleted"], 1);
        $this->assertSame($results["message"], "ok");

        $where_config = [
        "fields" => ["value"],
        "values" => [null],
        "matches" => ["="],
        "types" => ["i"]
        ];
        $results = $this->sql->removeV2("endoftestempty", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["status"], true);
        $this->assertSame($results["rowsDeleted"], 0);
        $this->assertSame($results["message"], "ok");
    }

    public function testRemoveMultiple()
    {
        $where_config = [
        "fields" => ["id"],
        "values" => [-1],
        "matches" => ["!="],
        "types" => ["i"]
        ];
        $results = $this->sql->removeV2("endoftestempty", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["status"], true);
        $this->assertSame($results["rowsDeleted"], 2);
        $this->assertSame($results["message"], "ok");

    }

    public function testRemoveLike()
    {
        $where_config = [
            "fields" => ["name"],
            "values" => ["pondblue"],
            "matches" => ["% LIKE %"],
            "types" => ["s"]
            ];
        $results = $this->sql->removeV2("liketests", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["message"], "ok");
        $this->assertSame($results["status"], true);
        $this->assertSame($results["rowsDeleted"], 2);
    }

    public function testRemoveBrokenSqlConnection()
    {
        $this->sql->sqlSave();
        $this->sql->dbUser = "invaild";
        $this->sql->dbPass = null;
        $where_config = [
            "fields" => ["name"],
            "values" => ["pondblue"],
            "matches" => ["% LIKE %"],
            "types" => ["s"]
            ];
        $results = $this->sql->removeV2("liketests", $where_config);
        //[rowsDeleted => int, status => bool, message => string]
        $this->assertSame($results["message"], "Connect attempt died in a fire");
        $this->assertSame($results["status"], false);
        $this->assertSame($results["rowsDeleted"], 0);
    }
}
