<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

class MysqliCountTest extends TestCase
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

    public function testCountOnehundo()
    {
        $results = $this->sql->basicCountV2("counttoonehundo");
        $this->assertSame($results["message"], "ok");
        $this->assertSame($results["status"], true);
        $this->assertSame($results["count"], 100);
    }

    public function testCountEmpty()
    {
        $results = $this->sql->basicCountV2("rollbacktest");
        $this->assertSame($results["message"], "ok");
        $this->assertSame($results["status"], true);
        $this->assertSame($results["count"], 0);
    }

    public function testCountNoTable()
    {
        $results = $this->sql->basicCountV2("");
        $this->assertSame($results["status"], false);
        $this->assertSame($results["count"], 0);
        $this->assertSame($results["message"], "No table given");
    }

    public function testCountInvaildTable()
    {
        $results = $this->sql->basicCountV2("badtable");
        $this->assertSame($results["status"], false);
        $this->assertSame($results["count"], 0);
        $this->assertSame($results["message"], "unable to prepair: Table 'test.badtable' doesn't exist");
    }

    public function testCountOnehundoOnlyNegitive()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [0],
            "matches" => ["<"],
            "types" => ["i"]
        ];
        $results = $this->sql->basicCountV2("counttoonehundo", $where_config);
        $this->assertSame($results["status"], true);
        $this->assertSame($results["count"], 0);
        $this->assertSame($results["message"], "ok");
    }

    public function testCountOnehundoOnlyIdsGtr60()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [60],
            "matches" => [">"],
            "types" => ["i"]
        ];
        $results = $this->sql->basicCountV2("counttoonehundo", $where_config);
        $this->assertSame($results["status"], true);
        $this->assertSame($results["count"], 40);
        $this->assertSame($results["message"], "ok");
    }

    public function testRemoveHasEmptyedCheckCount()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [-1],
            "matches" => ["!="],
            "types" => ["i"]
        ];
        $results = $this->sql->basicCountV2("endoftestempty", $where_config);
        $this->assertSame($results["status"], true);
        $this->assertSame($results["count"], 0);
        $this->assertSame($results["message"], "ok");
    }


    public function testAddHasAddedCheckCount()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [-1],
            "matches" => ["!="],
            "types" => ["i"]
        ];
        $results = $this->sql->basicCountV2("endoftestempty", $where_config);
        $this->assertSame($results["status"], true);
        $this->assertSame($results["count"], 0);
        $this->assertSame($results["message"], "ok");
    }
}
