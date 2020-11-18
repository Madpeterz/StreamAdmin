<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

class mysqli_add_test extends TestCase
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

    public function testAdd()
    {
        $loop = 0;
        while ($loop < 4) {
            $config = [
                "table" => "endoftestwithfourentrys",
                "fields" => ["value"],
                "values" => [sha1("testAdd" . $loop)],
                "types" => ["s"]
            ];
            $results = $this->sql->addV2($config);
            // [newID => ?int, rowsAdded => int, status => bool, message => string]
            $this->assertSame($results["message"], "ok");
            $this->assertSame($results["status"], true);
            $this->assertSame($results["rowsAdded"], 1);
            $this->assertGreaterThan(0, $results["newID"]);
            $loop++;
        }
    }

    public function test_add_invaildtable()
    {
        $config = [
            "table" => "badtable",
            "fields" => ["value"],
            "values" => ["testAdd1"],
            "types" => ["s"]
        ];
        $results = $this->sql->addV2($config);
        // [newID => ?int, rowsAdded => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["rowsAdded"], 0);
        $this->assertSame(
            $results["message"],
            "unable to prepair: Table 'test.badtable' doesn't exist"
        );
        $this->assertSame($results["newID"], null);
    }

    public function test_add_invaildfield()
    {
        $config = [
            "table" => "endoftestwithfourentrys",
            "fields" => ["badfield"],
            "values" => ["testAdd1"],
            "types" => ["s"]
        ];
        $results = $this->sql->addV2($config);
        // [newID => ?int, rowsAdded => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["rowsAdded"], 0);
        $error_msg = "unable to prepair: Unknown column 'badfield' in 'field list'";
        $this->assertSame($results["message"], $error_msg);
        $this->assertSame($results["newID"], null);
    }

    public function test_add_invaildvalue()
    {
        $config = [
            "table" => "endoftestwithfourentrys",
            "fields" => ["value"],
            "values" => [null],
            "types" => ["s"]
        ];
        $results = $this->sql->addV2($config);
        $this->assertSame($results["status"], false);
        $this->assertSame($results["rowsAdded"], 0);
        $error_msg = "unable to execute because: Column 'value' cannot be null";
        $this->assertSame($results["message"], $error_msg);
        $this->assertSame($results["newID"], null);

        $config = [
            "table" => "alltypestable",
            "fields" => ["stringfield","intfield", "floatfield"],
            "values" => [1.43, "44", 44.54],
            "types" => ["d", "s", "i"]
        ];
        $results = $this->sql->addV2($config);
        $this->assertSame($results["status"], true);
        $this->assertSame($results["rowsAdded"], 1);
        $this->assertSame($results["message"], "ok");
        $this->assertGreaterThan(0, $results["newID"]);
    }
}
