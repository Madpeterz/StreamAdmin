<?php

namespace Tests\Admin\Control;

use App\Endpoint\Control\Notice\Create;
use App\Endpoint\Control\Notice\Remove;
use App\Endpoint\Control\Notice\Update;
use App\Models\Notice;
use Tests\TestWorker;

class NoticeTest extends TestWorker
{
    public function test_Create()
    {
        $_POST["name"] = "unittest";
        $_POST["hoursRemaining"] = 856;
        $_POST["imMessage"] = "this is a unit test";
        $_POST["sendObjectIM"] = false;
        $_POST["noticeNotecardLink"] = 1;
        $_POST["useBot"] = false;
        $_POST["sendNotecard"] = false;
        $_POST["notecardDetail"] = "not used";
        $start = new Create();
        $start->process();
        $reply = $start->getOutputObject();
        $this->assertSame("Notice created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Create
     */
    public function test_Update()
    {
        $notice = new Notice();
        $notice->loadByHoursRemaining(856);
        $this->assertSame(true, $notice->isLoaded(), "Failed to load notice");
        global $system;
        $system->setPage($notice->getId());
        $_POST["name"] = "unittest";
        $_POST["hoursRemaining"] = 774;
        $_POST["imMessage"] = "this is a unit test woot";
        $_POST["sendObjectIM"] = false;
        $_POST["noticeNotecardLink"] = 1;
        $_POST["useBot"] = true;
        $_POST["sendNotecard"] = false;
        $_POST["notecardDetail"] = "wow much update";
        $start = new Update();
        $start->process();
        $reply = $start->getOutputObject();
        $this->assertSame("Notice updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        $notice = new Notice();
        $notice->loadByHoursRemaining(774);
        $this->assertSame(true, $notice->isLoaded(), "Failed to load notice");
        global $system;
        $system->setPage($notice->getId());
        $start = new Remove();
        $_POST["accept"] = "Accept";
        $_POST["newNoticeLevel"] = 5;
        $start->process();
        $reply = $start->getOutputObject();
        $this->assertSame("Notice removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}
