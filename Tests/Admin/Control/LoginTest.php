<?php

namespace Tests\Admin\Control;

use App\Endpoint\Control\Login\Reset;
use App\Endpoint\Control\Login\Resetnow;
use App\Endpoint\Control\Login\Start;
use App\Models\Staff;
use Tests\TestWorker;

class LoginTest extends TestWorker
{
    public function test_TestingStaff()
    {
        global $system;
        $staff = new Staff();
        $staff->setAvatarLink(2);
        $staff->setLhash("notused");
        $staff->setOwnerLevel(true);
        $staff->setEmailResetExpires(0);
        $staff->setUsername("unittesting");
        $staff->setPhash("notused");
        $staff->setPsalt("notused");
        $reply = $staff->createEntry();
        $this->assertSame(true, $reply->status, "failed to create staff object: " . $reply->message);
        $sessionHelper = $system->getSession();
        $sessionHelper->attachStaffMember($staff);
        $passwordchange = $sessionHelper->createStaffPasswordHash("iAmUnitMan");
        $staff->setPhash($passwordchange->phash);
        $staff->setPsalt($passwordchange->psalt);
        $update = $staff->updateEntry();
        $this->assertSame(true, $update->status, "failed to update staff entry");
    }
    /**
     * @depends test_TestingStaff
     */
    public function test_Start()
    {
        $_POST["staffusername"] = "unittesting";
        $_POST["staffpassword"] = "iAmUnitMan";
        $start = new Start();
        $start->process();
        $reply = $start->getOutputObject();
        $this->assertStringContainsString("logged in ^+^", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Start
     */
    public function test_Reset()
    {
        $_POST["slusername"] = "Madpeter Zond";
        $staff = new Staff();
        $staff->loadByAvatarLink(2);
        $this->assertSame(0, $staff->getEmailResetExpires(), "Reset code expires value not as expected");
        $reset = new Reset();
        $reset->process();
        $reply = $reset->getOutputObject();
        $this->assertStringContainsString("If the account was found the reset code is on the way.", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $staff = new Staff();
        $staff->loadByAvatarLink(2);
        $this->assertGreaterThan(time(), $staff->getEmailResetExpires(), "Reset code expires value should be higher than right now");
    }
    /**
     * @depends test_Reset
     */
    public function test_ResetNow()
    {
        $staff = new Staff();
        $staff->loadByAvatarLink(2);
        $this->assertGreaterThan(time(), $staff->getEmailResetExpires(), "Reset code expires value should be higher than right now");
        $reset = new Resetnow();
        $_POST["slusername"] = "Madpeter Zond";
        $_POST["token"] = $staff->getEmailResetCode();
        $_POST["newpassword1"] = "letmeinplease";
        $_POST["newpassword2"] = "letmeinplease";
        $reset->process();
        $reply = $reset->getOutputObject();
        $this->assertStringContainsString("Password updated please login", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $staff = new Staff();
        $staff->loadByAvatarLink(2);
        $this->assertSame(null, $staff->getEmailResetCode(), "Reset code should now be null");
    }
}
