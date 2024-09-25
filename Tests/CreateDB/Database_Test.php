<?php

namespace StreamAdminR7;

use App\Config;
use App\Models\Avatar;
use App\Models\Staff;
use PHPUnit\Framework\TestCase;

class Database_Test extends TestCase
{

    public function test_WipeDb()
    {
        global $system;
        $system = new Config();
        $results = $system->getSQL()->rawSQL("tests/wipeDB.sql");
        $this->assertSame("ok", $results->message, "incorrect wipe message: " . $system->getSQL()->getLastErrorBasic());
        $this->assertSame(5, $results->commandsRun, "incorrect number of commands run");
        $this->assertSame(true, $results->status, "wipe db has failed");
        $results = $system->getSQL()->rawSQL("Versions/installer.sql");
        $this->assertSame("ok", $results->message, "incorrect install message");
        $this->assertSame(122, $results->commandsRun, "incorrect number of commands run");
        $this->assertSame(true, $results->status, "install db has failed");
        $system->getSQL()->sqlSave(true);
    }


    /**
     * @depends test_WipeDb
     */
    public function test_CreateTestAccount()
    {
        $Avatar = new Avatar();
        $status = $Avatar->loadID(1);
        $this->assertSame(true, $status->status, "Failed to load avatar");
        $Avatar->setAvatarName("MadpeterUnit ZondTest");
        $Avatar->setAvatarUid("Madpeter");
        $Avatar->setAvatarUUID("b36971ef-b2a5-f461-025c-81bbc473deb8");
        $status = $Avatar->updateEntry();
        $this->assertSame(true, $status->status, "Failed to update");
        $staff = new Staff();
        $status = $staff->loadId(1);
        $this->assertSame(true, $status->status, "Failed to load avatar");
        $staff->setUsername("Madpeter");
        $status =  $staff->updateEntry();
        $this->assertSame(true, $status->status, "Failed to update");
    }
}
