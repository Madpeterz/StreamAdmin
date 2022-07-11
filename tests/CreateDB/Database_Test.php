<?php

namespace StreamAdminR7;

use PHPUnit\Framework\TestCase;

class Database_Test extends TestCase
{

    public function test_WipeDb()
    {
        global $system;
        $results = $system->getSQL()->rawSQL("tests/wipeDB.sql");
        $this->assertSame("ok", $results->message, "incorrect wipe message");
        $this->assertSame(5, $results->commandsRun, "incorrect number of commands run");
        $this->assertSame(true, $results->status, "wipe db has failed");
        $system->getSQL()->sqlSave();
        $results = $system->getSQL()->rawSQL("Versions/installer.sql");
        $this->assertSame("ok", $results->message, "incorrect install message");
        $this->assertSame(140, $results->commandsRun, "incorrect number of commands run");
        $this->assertSame(true, $results->status, "install db has failed");
    }


    /**
     * @depends test_Getconfig
     */
}
