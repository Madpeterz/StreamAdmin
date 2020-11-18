<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

class MysqliSearchTest extends TestCase
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

    public function testSearchOk()
    {
        $tables = ["twintables1","twintables2"];
        $results = $this->sql->searchTables($tables, "title", "harry potter", "s", "=", 99, "id");
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($results["message"], "ok");
        $this->assertSame($results["status"], true);
        $this->assertSame(count($results["dataset"]), 2);
    }

    public function testSearchNoMatchs()
    {
        $tables = ["twintables1","twintables2"];
        $results = $this->sql->searchTables($tables, "message", "none", "s", "=", 99, "id");
        $this->assertSame($results["message"], "ok");
        $this->assertSame($results["status"], true);
        $this->assertSame(count($results["dataset"]), 0);
    }

    public function testSearchMissingField()
    {
        $tables = ["twintables1","twintables2"];
        $results = $this->sql->searchTables($tables, "notafield", "none", "s", "=", 99, "id");
        $this->assertSame($results["message"], "unable to prepair: Unknown column 'tb1.notafield' in 'where clause'");
        $this->assertSame($results["status"], false);
        $this->assertSame(count($results["dataset"]), 0);
    }

    public function testSearchMissingTable()
    {
        $tables = ["notatable","twintables2"];
        $results = $this->sql->searchTables($tables, "title", "harry potter", "s", "=", 99, "id");
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($results["message"], "unable to prepair: Table 'test.notatable' doesn't exist");
        $this->assertSame($results["status"], false);
        $this->assertSame(count($results["dataset"]), 0);
    }

    public function testSearchOnly1Table()
    {
        $tables = ["notatable"];
        $results = $this->sql->searchTables($tables, "title", "harry potter", "s", "=", 99, "id");
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($results["message"], "Requires 2 or more tables to use search");
        $this->assertSame($results["status"], false);
        $this->assertSame(count($results["dataset"]), 0);
    }

    public function testSearchEmptyMatchField()
    {
        $tables = ["notatable","twintables2"];
        $results = $this->sql->searchTables($tables, "", "harry potter", "s", "=", 99, "id");
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($results["message"], "Requires a match field to be sent");
        $this->assertSame($results["status"], false);
        $this->assertSame(count($results["dataset"]), 0);
    }

    public function testSearchInvaildMatchSqlType()
    {
        $tables = ["notatable","twintables2"];
        $results = $this->sql->searchTables($tables, "title", "harry potter", "q", "=", 99, "id");
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($results["message"], "Match type is not vaild");
        $this->assertSame($results["status"], false);
        $this->assertSame(count($results["dataset"]), 0);
    }

    public function testSearchIsNull()
    {
        $tables = ["twintables1","twintables2"];
        $results = $this->sql->searchTables($tables, "title", null, "s", "IS", 99, "id");
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($results["message"], "ok");
        $this->assertSame($results["status"], true);
        $this->assertSame(count($results["dataset"]), 0);
    }

    public function testSearchNullValueNoIs()
    {
        $tables = ["twintables1","twintables2"];
        $results = $this->sql->searchTables($tables, "title", null, "s", "=", 99, "id");
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($results["message"], "Match value can not be null");
        $this->assertSame($results["status"], false);
        $this->assertSame(count($results["dataset"]), 0);
    }

    public function testSearchNoSqlConnection()
    {
        $this->sql->shutdown();
        $this->sql->dbUser = "invaild";
        $this->sql->dbPass = null;
        $tables = ["twintables1","twintables2"];
        $results = $this->sql->searchTables($tables, "title", "title", "s", "=", 99, "id");
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($results["message"], "Connect attempt died in a fire");
        $this->assertSame($results["status"], false);
        $this->assertSame(count($results["dataset"]), 0);
    }
}
