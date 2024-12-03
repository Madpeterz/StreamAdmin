<?php

namespace Tests\Admin\Control;

use App\Endpoint\Control\Bot\Update;
use App\Models\Staff;
use Tests\TestWorker;

class BotTest extends TestWorker
{
    public function test_Update()
    {
        global $system;
        $staff = new Staff();
        $staff->setOwnerLevel(true);
        $staff->setAvatarLink(1);
        $system->getSession()->attachStaffMember($staff);
        $botUpdate = new Update();
        $_POST["avataruid"] = "SysDevOp";
        $_POST["secret"] = "yeppers";
        $_POST["httpMode"] = 0;
        $_POST["httpURL"] = "";
        $_POST["notecards"] = 1;
        $_POST["ims"] = 1;
        $_POST["invites"] = 0;
        $_POST["inviteGroupUUID"] = "";

        $botUpdate->process();
        $reply = $botUpdate->getOutputObject();
        $this->assertSame("Changes saved", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
}
