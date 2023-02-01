<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Notice\Create as NoticeCreate;
use App\Endpoint\Control\Notice\Remove as NoticeRemove;
use App\Endpoint\Control\Notice\Update;
use App\Endpoint\View\Notice\Create;
use App\Endpoint\View\Notice\DefaultView;
use App\Endpoint\View\Notice\Manage;
use App\Endpoint\View\Notice\Remove;
use App\Models\Notice;
use App\Models\Sets\RentalSet;
use PHPUnit\Framework\TestCase;

class NoticeTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Notice element";
        $this->assertStringContainsString("7 day notice",$statuscheck,$missing);
        $this->assertStringContainsString("3 day notice",$statuscheck,$missing);
        $this->assertStringContainsString("Expired",$statuscheck,$missing);
        $this->assertStringContainsString("120",$statuscheck,$missing);
        $this->assertStringContainsString("24",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_CreateForm()
    {
        $createForm = new Create();
        $createForm->process();
        $statuscheck = $createForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Notice create form element";
        $this->assertStringContainsString("[[RENTAL_TIMELEFT]]",$statuscheck,$missing);
        $this->assertStringContainsString("Tag",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
        $this->assertStringContainsString("Notecard content",$statuscheck,$missing);
        $this->assertStringContainsString("Static notecard",$statuscheck,$missing);
        $this->assertStringContainsString("Hours remain [Trigger at]",$statuscheck,$missing);
        $this->assertStringContainsString("Basic",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $_POST["name"] = "UnitTest";
        $_POST["hoursRemaining"] = "15";
        $_POST["imMessage"] = "This is a test it is only a test";
        $_POST["useBot"] = "true";
        $_POST["sendNotecard"] = "true";
        $_POST["notecardDetail"] = "this is a test wooo";
        $_POST["noticeNotecardLink"] = 1;
        $_POST["sendObjectIM"] = 1;

        $createHandler = new NoticeCreate();
        $createHandler->process();
        $statuscheck = $createHandler->getOutputObject();
        $this->assertStringContainsString("Notice created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $testsystem;
        $notice = new Notice();
        $status = $notice->loadByName("UnitTest");
        $this->assertSame(true,$status->status,"Unable to load testing notice");
        $testsystem->setPage($notice->getId());

        $manageForm = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Notice manage form element";
        $this->assertStringContainsString("[[RENTAL_TIMELEFT]]",$statuscheck,$missing);
        $this->assertStringContainsString("Tag",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
        $this->assertStringContainsString("Notecard content",$statuscheck,$missing);
        $this->assertStringContainsString("Static notecard",$statuscheck,$missing);
        $this->assertStringContainsString("Hours remain [Trigger at]",$statuscheck,$missing);
        $this->assertStringContainsString("Basic",$statuscheck,$missing);
        $this->assertStringContainsString("this is a test wooo",$statuscheck,$missing);
        $this->assertStringContainsString("This is a test it is only a test",$statuscheck,$missing);
        $this->assertStringContainsString("UnitTest",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $testsystem, $_POST;
        $notice = new Notice();
        $status = $notice->loadByName("UnitTest");
        $this->assertSame(true,$status->status,"Unable to load testing notice");
        $testsystem->setPage($notice->getId());

        $_POST["name"] = "UnitTest Updated";
        $_POST["hoursRemaining"] = "90";
        $_POST["imMessage"] = "This is a test it is only a test";
        $_POST["useBot"] = "false";
        $_POST["sendNotecard"] = "false";
        $_POST["notecardDetail"] = "";
        $_POST["noticeNotecardLink"] = 1;
        $_POST["sendObjectIM"] = 0;

        $updateHandeler = new Update();
        $updateHandeler->process();
        $statuscheck = $updateHandeler->getOutputObject();
        $this->assertStringContainsString("Notice updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveForm()
    {
        global $testsystem;
        $notice = new Notice();
        $status = $notice->loadByName("UnitTest Updated");
        $this->assertSame(true,$status->status,"Unable to load testing notice");
        $testsystem->setPage($notice->getId());

        $removeForm = new Remove();
        $removeForm->process();

        $statuscheck = $removeForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Notice remove form element";
        $this->assertStringContainsString("This action will fail if the Notice",$statuscheck,$missing);
        $this->assertStringContainsString("Active",$statuscheck,$missing);
        $this->assertStringContainsString("Remove",$statuscheck,$missing);
        $this->assertStringContainsString("Accept",$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveForm
     */
    public function test_RemoveProcess()
    {
        global $testsystem, $_POST;
        $notice = new Notice();
        $status = $notice->loadByName("UnitTest Updated");
        $this->assertSame(true,$status->status,"Unable to load testing notice");


        $rentalSet = new RentalSet();
        $rentalSet->loadNewest(1);
        $this->assertGreaterThan(0,$rentalSet->getCount(),"No rentals!");
        $rental = $rentalSet->getFirst();
        $rental->setNoticeLink($notice->getId());
        $reply = $rental->updateEntry();
        $this->assertSame(true,$reply->status,"Failed to assign rental to notice");


        $testsystem->setPage($notice->getId());
        $_POST["accept"] = "Accept";
        $_POST["newNoticeLevel"] = 10;

        $removeHandeler = new NoticeRemove();
        $removeHandeler->process();
        $statuscheck = $removeHandeler->getOutputObject();
        $this->assertStringContainsString("Notice removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
