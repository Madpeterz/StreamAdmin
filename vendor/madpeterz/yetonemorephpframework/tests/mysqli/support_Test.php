<?php

namespace YAPFtest;

use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;

class MysqliSupportTest extends TestCase
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

    public function testLastSql()
    {
        $this->assertSame($this->sql->getLastSQl(), "");
        $config = [
            "table" => "endoftestwithfourentrys",
            "fields" => ["value"],
            "values" => [sha1("testAdd888")],
            "types" => ["s"]
        ];
        $this->sql->addV2($config);
        $this->assertSame($this->sql->getLastSQl(), "INSERT INTO endoftestwithfourentrys (value) VALUES (?)");
    }

    public function testAddv2MissingKey()
    {
        $result = $this->sql->addV2();
        $this->assertSame($result["message"], "Required key: table is missing");
        $this->assertSame($result["status"], false);
        $this->assertSame($result["newID"], null);
        $this->assertSame($result["rowsAdded"], 0);
    }

    public function testAddv2IncorrectFieldstoValues()
    {
        $config = [
            "table" => "endoftestwithfourentrys",
            "fields" => ["value","asdasda"],
            "values" => [sha1("testAdd888")],
            "types" => ["s"]
        ];
        $result = $this->sql->addV2($config);
        $this->assertSame($result["message"], "fields and values counts do not match!");
        $this->assertSame($result["status"], false);
        $this->assertSame($result["newID"], null);
        $this->assertSame($result["rowsAdded"], 0);
    }
    public function testAddv2IncorrectValuesToTypes()
    {
        $config = [
            "table" => "endoftestwithfourentrys",
            "fields" => ["value"],
            "values" => [sha1("testAdd888")],
            "types" => ["s","asdasda"]
        ];
        $result = $this->sql->addV2($config);
        $this->assertSame($result["message"], "values and types counts do not match!");
        $this->assertSame($result["status"], false);
        $this->assertSame($result["newID"], null);
        $this->assertSame($result["rowsAdded"], 0);
    }

    public function testAddv2SqlStartupError()
    {
        $this->sql->sqlSave(true);
        $this->sql->dbName = "invaild";
        $config = [
            "table" => "endoftestwithfourentrys",
            "fields" => ["value"],
            "values" => [sha1("testAdd888")],
            "types" => ["s"]
        ];
        $result = $this->sql->addV2($config);
        $this->assertSame($result["message"], "Connect attempt died in a fire");
        $this->assertSame($result["status"], false);
        $this->assertSame($result["newID"], null);
        $this->assertSame($result["rowsAdded"], 0);
    }

    public function testMysqliCoreDestruct()
    {
        $startup = $this->sql->sqlStart();
        $this->assertSame($startup, true);
        $result = $this->sql->shutdown();
        $this->assertSame($result, true);
        $this->assertSame($this->sql->getLastErrorBasic(), "No changes made");
        $result = $this->sql->shutdown();
        $this->assertSame($result, true);
        $this->assertSame($this->sql->getLastErrorBasic(), "Not connected");
    }

    public function testMysqliRawSql()
    {
        // comments only
        $result = $this->sql->rawSQL("tests/mysqli/testRawSQL_Commentsonly.sql");
        $this->assertSame($this->sql->getLastSQl(), "");
        $this->assertSame($this->sql->getLastErrorBasic(), "No commands processed from file");
        $this->assertSame($result["status"], false);
        $this->sql->sqlSave(true);
        // missing ; on end
        $result = $this->sql->rawSQL("tests/mysqli/testRawSQL_Noending.sql");
        $this->assertSame($this->sql->getLastErrorBasic(), "Warning: raw sql has no ending ;");
        $this->assertSame($result["status"], true);
        $this->sql->sqlSave(true);
        // empty
        $result = $this->sql->rawSQL("tests/mysqli/testRawSQL_Empty.sql");
        $this->assertSame($this->sql->getLastErrorBasic(), "File is empty");
        $this->assertSame($result["status"], false);
        $this->sql->sqlSave(true);
        // very broken
        $result = $this->sql->rawSQL("tests/mysqli/testRawSQL_Malformed.sql");
        $error_msg = "raw sql failed in some way maybe error message can help: \n";
        $error_msg .= "You have an error in your SQL syntax; check the manual ";
        $error_msg .= "that corresponds to your MariaDB server version for the right ";
        $error_msg .= "syntax to use near 'WHERE id != 4' at line 1";
        if (strpos($this->sql->getLastErrorBasic(), "MariaDB") === false) {
            $error_msg = strtr($error_msg, ["MariaDB" => "MySQL"]);
        }
        $this->assertSame($this->sql->getLastErrorBasic(), $error_msg);
        $this->assertSame($result["status"], false);
        $this->sql->sqlSave(true);
        // no SQL connection
        $this->sql->dbName = "invaild";
        $result = $this->sql->rawSQL("tests/mysqli/testRawSQL_Noending.sql");
        $this->assertSame($this->sql->getLastErrorBasic(), "Unable to start SQL");
        $this->assertSame($result["status"], false);
    }

    public function testMysqliCountNoData()
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [-1],
            "types" => ["i"],
            "matches" => ["<="],
        ];
        $result = $this->sql->basicCountV2("alltypestable", $where_config);
        $this->assertSame($result["count"], 0);
        $this->assertSame($result["message"], "ok");
        $this->assertSame($result["status"], true);
    }

    public function testFlagErrorRollback()
    {
        $result = $this->sql->basicCountV2("rollbacktest");
        $this->assertSame($result["count"], 0);
        $this->assertSame($result["status"], true);
        $config = [
            "table" => "rollbacktest",
            "fields" => ["name","value"],
            "values" => ["kilme",12],
            "types" => ["s","i"],
        ];
        $results = $this->sql->addV2($config);
        $this->assertSame($results["status"], true);
        $this->assertSame($results["rowsAdded"], 1);
        $this->assertSame($results["message"], "ok");
        $this->assertSame($results["newID"], 1);
        $this->sql->flagError();
        $this->sql->sqlSave(); // reject save due to error and rollback
        $result = $this->sql->basicCountV2("rollbacktest");
        $this->assertSame($result["count"], 0);
        $this->assertSame($result["status"], true);
        $results = $this->sql->addV2($config);
        $this->assertSame($results["status"], true);
        $this->assertSame($results["rowsAdded"], 1);
        $this->assertSame($results["message"], "ok");
        $this->assertSame($results["newID"], 2);
        $this->sql->sqlRollBack(); // force a rollback now
        $result = $this->sql->basicCountV2("rollbacktest");
        $this->assertSame($result["count"], 0);
        $this->assertSame($result["status"], true);
    }

    public function testConnectOtherhost()
    {
        // bad host / bad details / bad db
        $result = $this->sql->sqlStartConnection("testsuser", "testsuserPW", "test", true, "magicmadpeter.xyz", 1);
        $this->assertSame($result, false);
        $this->assertSame($this->sql->getLastErrorBasic(), "Connect attempt died in a fire");
        // good host / bad details / good db
        $result = $this->sql->sqlStartConnection("fakeuser", "fakepassword", "test", true, "127.0.0.1", 1);
        $this->assertSame($result, false);
        $this->assertSame($this->sql->getLastErrorBasic(), "Connect attempt died in a fire");
        // good host / good details / bad DB
        $this->sql->fullSqlErrors = true;
        $result = $this->sql->sqlStartConnection("testsuser", "testsuserPW", "fakedbname", true, "127.0.0.1", 1);
        $this->assertSame($result, false);
        $error_msg = "";
        if (strpos($this->sql->getLastErrorBasic(), "HY000/1049") === false) {
            $error_msg = "SQL connection error: mysqli_real_connect(): ";
            $error_msg .= "(HY000/1044): Access denied for user 'testsuser'@'%' to database 'fakedbname'";
        } else {
            $error_msg = "SQL connection error: mysqli_real_connect(): ";
            $error_msg .= "(HY000/1049): Unknown database 'fakedbname'";
        }
        $this->assertSame($this->sql->getLastErrorBasic(), $error_msg);
        // good host / good details / good DB
        $this->sql->fullSqlErrors = false;
        $result = $this->sql->sqlStartConnection("testsuser", "testsuserPW", "information_schema", true);
        $this->assertSame($result, true);
    }

    public function testSqlStartBadConfig()
    {
        $savedbuser = $this->sql->dbUser;
        $this->sql->dbUser = null;
        $result = $this->sql->sqlStart();
        $this->assertSame($this->sql->getLastErrorBasic(), "DB config is not vaild to start!");
        $this->assertSame($result, false);
        $this->sql->dbUser = $savedbuser;
        $this->sql->dbPass = null;
        $result = $this->sql->sqlStart();
        $this->assertSame($this->sql->getLastErrorBasic(), "Connect attempt died in a fire");
        $this->assertSame($result, false);
    }

    public function testSqlSelectBadBinds()
    {
        $basic_config = ["table" => "counttoonehundo"];
        $where_config = [
            "fields" => ["cvalue"],
            "values" => [256],
            "types" => ["tttt"],
            "matches" => ["<"],
        ];
        $result = $this->sql->selectV2($basic_config, null, $where_config);
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($result["message"], "Unable to bind to statement");
        $this->assertSame($result["status"], false);
        $this->sql->fullSqlErrors = true;
        $result = $this->sql->selectV2($basic_config, null, $where_config);
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $full_bind_error = "Unable to bind to statement: mysqli_stmt_bind_param(): Number of elements ";
        $full_bind_error .= "in type definition string doesn't match number of bind variables";
        $this->assertSame($result["message"], $full_bind_error);
        $this->assertSame($result["status"], false);
    }

    public function testSqlSelectEmptyWhereConfig()
    {
        $result = $this->sql->selectV2(["table" => "example"], null, []);
        $this->assertSame($result["message"], "Where config failed: where_config is empty but not null!");
        $this->assertSame($result["status"], false);
    }
    public function testSqlSelectWhereConfigMissingKeys()
    {
        $result = $this->sql->selectV2(["table" => "example"], null, ["fields" => ["lol"]]);
        $this->assertSame($result["message"], "Where config failed: missing where keys:values,types,matches");
        $this->assertSame($result["status"], false);
    }

    public function testSelectWhereConfigFieldsToValueError()
    {
        $basic_config = ["table" => "counttoonehundo"];
        $where_config = [
            "fields" => ["id"],
            "values" => [14,44],
            "types" => ["s"],
            "matches" => ["="]
        ];
        $result = $this->sql->selectV2($basic_config, null, $where_config);
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($result["message"], "Where config failed: count error fields <=> values");
        $this->assertSame(count($result["dataset"]), 0);
        $this->assertSame($result["status"], false);
    }

    public function testSelectWhereConfigValueToTypeError()
    {
        $basic_config = ["table" => "counttoonehundo"];
        $where_config = [
            "fields" => ["id"],
            "values" => [14],
            "types" => ["s","i"],
            "matches" => ["="]
        ];
        $result = $this->sql->selectV2($basic_config, null, $where_config);
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($result["message"], "Where config failed: count error values <=> types");
        $this->assertSame(count($result["dataset"]), 0);
        $this->assertSame($result["status"], false);
    }

    public function testSelectWhereConfigTypetoMatchsError()
    {
        $basic_config = ["table" => "counttoonehundo"];
        $where_config = [
            "fields" => ["id"],
            "values" => [14],
            "types" => ["s"],
            "matches" => ["=","<="]
        ];
        $result = $this->sql->selectV2($basic_config, null, $where_config);
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($result["message"], "Where config failed: count error types <=> matches");
        $this->assertSame(count($result["dataset"]), 0);
        $this->assertSame($result["status"], false);
    }

    public function testSelectWhereConfigExtraJoinWiths()
    {
        $basic_config = ["table" => "counttoonehundo"];
        $where_config = [
            "fields" => ["id"],
            "values" => [14],
            "types" => ["s"],
            "matches" => ["="],
            "join_with" => ["OR","AND"]
        ];
        $result = $this->sql->selectV2($basic_config, null, $where_config);
        // [dataset => mixed[mixed[]], status => bool, message => string]
        $this->assertSame($result["message"], "Where config failed: where_config join_with count error");
        $this->assertSame(count($result["dataset"]), 0);
        $this->assertSame($result["status"], false);
    }
}
