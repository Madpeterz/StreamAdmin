<?php

namespace Tests\Secondlife;

use App\Endpoint\Control\Bot\Update;
use App\Endpoint\Secondlifeapi\Bot\Notecardsync;
use App\Models\Botcommandq;
use App\Models\Staff;
use Tests\TestWorker;

class BotEndpointTest extends TestWorker
{
    public function test_NoTasks()
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

        $Notecardsync = new Notecardsync();
        $Notecardsync->setOwnerOverride(true);
        $Notecardsync->process();
        $reply = $Notecardsync->getOutputObject();
        $this->assertSame("nowork", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
    /**
     * @depends test_NoTasks
     */
    public function test_ImTask()
    {
        $botcommandq = new Botcommandq();
        $botcommandq->setCommand("FetchNextNotecard");
        $botcommandq->setArgs("configstuff");
        $botcommandq->setUnixtime(time());
        $create = $botcommandq->createEntry();
        $this->assertSame(true,$create->status,"failed to create work for command q");
        $this->assertSame("ok",$create->message,"failed to create work for command q");

        $Notecardsync = new Notecardsync();
        $Notecardsync->setOwnerOverride(true);
        $Notecardsync->process();
        $reply = $Notecardsync->getOutputObject();
        $this->assertSame("ok", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
}
