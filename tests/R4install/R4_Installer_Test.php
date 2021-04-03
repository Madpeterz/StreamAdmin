<?php

namespace StreamAdminR7;

use App\Endpoint\View\Install\DefaultView as InstallerStep1;
use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled;

class R4_Installer extends TestCase
{
    public function test_ProcessFormEnterDatabaseDetails()
    {
        $sqlR4 = new MysqliEnabled();
        $openConnection = $sqlR4->sqlStartConnection("testsuser","testsuserPW","r4test",false,"127.0.0.1",10);
        $this->assertSame(true,$openConnection,"Unable to open SQL connection to create r4 test DB");
        $status = $sqlR4->rawSQL("tests/r4TestDB.sql");
        $this->assertSame(true,$status["status"],"Unable to import R4 database");
        $this->assertSame(true,$sqlR4->sqlSave(true),"Unable to save SQL dataset");
    }
}
