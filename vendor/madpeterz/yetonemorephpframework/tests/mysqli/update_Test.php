<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

class MysqliUpdateTest extends TestCase
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

    public function testUpdate()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["i"],
        ];
        $update_config = [
            "fields" => ["username"],
            "values" => ["NotMadpeter"],
            "types" => ["s"],
        ];
        $results = $this->sql->updateV2("endoftestwithupdates", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame("ok", $results["message"], "Incorrect update status message: ".$this->sql->getLastSql());
        $this->assertSame($results["status"], true);
        $this->assertSame($results["changes"], 1);
        
    }

    public function testUpdateNoTypes()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["i"],
        ];
        $update_config = [
            "fields" => ["username"],
            "values" => ["NotMadpeter"],
            "types" => [],
        ];
        $results = $this->sql->updateV2("endoftestwithupdates", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["changes"], 0);
        $this->assertSame($results["message"], "No types given for update");
    }

    public function testUpdateBadUpdateConfigs()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["i"],
        ];
        $update_config = [
            "fields" => ["username"],
            "values" => [],
            "types" => ["s"],
        ];
        $results = $this->sql->updateV2("endoftestwithupdates", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["changes"], 0);
        $this->assertSame($results["message"], "count issue fields <=> values");
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["i"],
        ];
        $update_config = [
            "fields" => ["username"],
            "values" => ["lol"],
            "types" => ["s","i"],
        ];
        $results = $this->sql->updateV2("endoftestwithupdates", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["changes"], 0);
        $this->assertSame($results["message"], "count issue values <=> types");
    }

    public function testUpdateInvaildTable()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["i"],
        ];
        $update_config = [
            "fields" => ["username"],
            "values" => ["NotMadpeter"],
            "types" => ["s"],
        ];
        $results = $this->sql->updateV2("badtable", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["changes"], 0);
        $this->assertSame($results["message"], "unable to prepair: Table 'test.badtable' doesn't exist");

        $results = $this->sql->updateV2("", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["changes"], 0);
        $this->assertSame($results["message"], "No table given");
    }

    public function testUpdateInvaildField()
    {
        $where_config = [
            "fields" => ["missingfield"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["s"]
        ];
        $update_config = [
            "fields" => ["username"],
            "values" => ["NotMadpeter"],
            "types" => ["s"]
        ];
        $results = $this->sql->updateV2("endoftestwithupdates", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["changes"], 0);
        $this->assertSame($results["message"], "unable to prepair: Unknown column 'missingfield' in 'where clause'");
    }

    public function testUpdateInvaildValue()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["i"]
        ];
        $update_config = [
            "fields" => ["username"],
            "values" => [null],
            "types" => ["s"]
        ];
        $results = $this->sql->updateV2("endoftestwithupdates", $update_config, $where_config);
        if($results["message"] == "ok") {
            $this->addWarning("Your mysql server is not setup in strict mode\n 
            change sql_mode=NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION to STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER");
        }
        $this->assertSame($results["message"], "unable to execute because: Column 'username' cannot be null");
        $this->assertSame($results["status"], false);
        $this->assertSame($results["changes"], 0);
    }

    public function testUpdateMultiple()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [-1],
            "matches" => ["!="],
            "types" => ["i"]
        ];
        $update_config = [
            "fields" => ["value"],
            "values" => ["magic"],
            "types" => ["s"]
        ];
        $results = $this->sql->updateV2("liketests", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], true);
        $this->assertSame($results["changes"], 2);
        $this->assertSame($results["message"], "ok");
    }

    public function testUpdateLike()
    {
        $where_config = [
            "fields" => ["name"],
            "values" => ["Advent"],
            "matches" => ["% LIKE %"],
            "types" => ["s"]
        ];
        $update_config = [
            "fields" => ["value"],
            "values" => ["woof"],
            "types" => ["s"]
        ];
        $results = $this->sql->updateV2("liketests", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], true);
        $this->assertSame($results["changes"], 2);
        $this->assertSame($results["message"], "ok");
    }

    public function testUpdateNoSqlConnection()
    {
        $this->sql->sqlSave();
        $this->sql->dbUser = "invaild";
        $this->sql->dbPass = null;
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "matches" => ["="],
            "types" => ["i"],
        ];
        $update_config = [
            "fields" => ["username"],
            "values" => ["NotMadpeter"],
            "types" => ["s"],
        ];
        $results = $this->sql->updateV2("endoftestwithupdates", $update_config, $where_config);
        // [changes => int, status => bool, message => string]
        $this->assertSame($results["status"], false);
        $this->assertSame($results["changes"], 0);
        $this->assertSame($results["message"], "Connect attempt died in a fire");
    }
}
