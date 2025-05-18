<?php

namespace Tests\Admin\Control;

use App\Endpoint\Control\Staff\Create;
use App\Endpoint\Control\Staff\Remove;
use App\Endpoint\Control\Staff\Update;
use Tests\SessionControlTesting;
use Tests\TestWorker;

class StaffTest extends TestWorker
{
    public function test_Create()
    {
        global $system;
        $system->attachSession(new SessionControlTesting());
        $_POST["avataruid"] = "SysDevOp";
        $_POST["username"] = "Unittester";
        $staff = new Create();
        $staff->process();
        $reply = $staff->getOutputObject();
        $this->assertSame("Staff member created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Create
     */
    public function test_Update()
    {
        global $system;
        $system->setPage(2);
        $_POST["username"] = "Iammagic";
        $staff = new Update();
        $staff->process();
        $reply = $staff->getOutputObject();
        $this->assertSame("Staff member updated passwords reset", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
        /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        global $system;
        $system->setPage(2);
        $staff = new Remove();
        $staff->process();
        $reply = $staff->getOutputObject();
        $this->assertSame("Staff member removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}
