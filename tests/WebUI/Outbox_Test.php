<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Outbox\Send;
use App\Endpoint\View\Outbox\Api;
use App\Endpoint\View\Outbox\Bulk;
use App\Endpoint\View\Outbox\DefaultView;
use App\Endpoint\View\Outbox\Details;
use App\Endpoint\View\Outbox\Mail;
use App\Endpoint\View\Outbox\Notecard;
use App\R7\Set\MessageSet;
use PHPUnit\Framework\TestCase;

class OutboxText extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $missing = "Missing outbox element";
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Status",$statuscheck,$missing);
        $this->assertStringContainsString("Pending",$statuscheck,$missing);
        $this->assertStringContainsString("api",$statuscheck,$missing);
        $this->assertStringContainsString("object is running normaly",$statuscheck,$missing);
        $this->assertStringContainsString("Package",$statuscheck,$missing);
        $this->assertStringContainsString("Server",$statuscheck,$missing);
        $this->assertStringContainsString("Notice",$statuscheck,$missing);
        $this->assertStringContainsString("Swaps",$statuscheck,$missing);
        $this->assertStringContainsString("[[PACKAGE_AUTODJ]]",$statuscheck,$missing);
        $this->assertStringContainsString("Select avatars",$statuscheck,$missing);
    }

    public function test_OutboxNotecard()
    {
        $notecards = new Notecard();
        $notecards->process();
        $missing = "Missing outbox element";
        $statuscheck = $notecards->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Avatar name",$statuscheck,$missing);
    }

    public function test_OutboxDetails()
    {
        $details = new Details();
        $details->process();
        $missing = "Missing outbox element";
        $statuscheck = $details->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Avatar name",$statuscheck,$missing);
    }

    public function test_OutboxMail()
    {
        $details = new Mail();
        $details->process();
        $missing = "Missing outbox element";
        $statuscheck = $details->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Avatar name",$statuscheck,$missing);
        $this->assertStringContainsString("Start of message",$statuscheck,$missing);
    }

    public function test_OutboxApi()
    {
        $details = new Api();
        $details->process();
        $missing = "Missing outbox element";
        $statuscheck = $details->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Event",$statuscheck,$missing);
        $this->assertStringContainsString("Attempts",$statuscheck,$missing);
        $this->assertStringContainsString("Port",$statuscheck,$missing);
    }

    public function test_BulkSendToPackageForm()
    {
        global $_GET, $page;
        $page = "package";
        $_GET["packageLink"] = 1;
        $_GET["message"] = "Hello world this is a test";

        $messagecheck = 'name="message" value="'.$_GET["message"].'"';
        $sourcecheck = 'name="source" value="package"';
        $sourcevaluecheck = 'name="source_id" value="1"';
        $avatarcheck = 'name="max_avatars" value="1"';
        $checkboxcheck = 'id="avatarmail1" name="avatarids[]" value="1"';

        $bulkPackage = new Bulk();
        $bulkPackage->process();
        $missing = "Missing outbox bulk package element";
        $statuscheck = $bulkPackage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("X",$statuscheck,$missing);
        $this->assertStringContainsString("Send to selected",$statuscheck,$missing);
        $this->assertStringContainsString($messagecheck,$statuscheck,$missing);
        $this->assertStringContainsString($sourcecheck,$statuscheck,$missing);
        $this->assertStringContainsString($sourcevaluecheck,$statuscheck,$missing);
        $this->assertStringContainsString($avatarcheck,$statuscheck,$missing);
        $this->assertStringContainsString($checkboxcheck,$statuscheck,$missing);
    }

    public function test_BulkSendToServerForm()
    {
        global $_GET, $page;
        $page = "server";
        $_GET["serverLink"] = 1;
        $_GET["message"] = "Hello world this is a test";

        $messagecheck = 'name="message" value="'.$_GET["message"].'"';
        $sourcecheck = 'name="source" value="server"';
        $sourcevaluecheck = 'name="source_id" value="1"';
        $avatarcheck = 'name="max_avatars" value="1"';
        $checkboxcheck = 'id="avatarmail1" name="avatarids[]" value="1"';

        $bulkPackage = new Bulk();
        $bulkPackage->process();
        $missing = "Missing outbox bulk server element";
        $statuscheck = $bulkPackage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("X",$statuscheck,$missing);
        $this->assertStringContainsString("Send to selected",$statuscheck,$missing);
        $this->assertStringContainsString($messagecheck,$statuscheck,$missing);
        $this->assertStringContainsString($sourcecheck,$statuscheck,$missing);
        $this->assertStringContainsString($sourcevaluecheck,$statuscheck,$missing);
        $this->assertStringContainsString($avatarcheck,$statuscheck,$missing);
        $this->assertStringContainsString($checkboxcheck,$statuscheck,$missing);
    }

    public function test_BulkSendToNoticeForm()
    {
        global $_GET, $page;
        $page = "notice";
        $_GET["noticeLink"] = 1;
        $_GET["message"] = "Hello world this is a test";

        $messagecheck = 'name="message" value="'.$_GET["message"].'"';
        $sourcecheck = 'name="source" value="notice"';
        $sourcevaluecheck = 'name="source_id" value="1"';
        $avatarcheck = 'name="max_avatars" value="1"';
        $checkboxcheck = 'id="avatarmail1" name="avatarids[]" value="1"';

        $bulkPackage = new Bulk();
        $bulkPackage->process();
        $missing = "Missing outbox bulk server element";
        $statuscheck = $bulkPackage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("X",$statuscheck,$missing);
        $this->assertStringContainsString("Send to selected",$statuscheck,$missing);
        $this->assertStringContainsString($messagecheck,$statuscheck,$missing);
        $this->assertStringContainsString($sourcecheck,$statuscheck,$missing);
        $this->assertStringContainsString($sourcevaluecheck,$statuscheck,$missing);
        $this->assertStringContainsString($avatarcheck,$statuscheck,$missing);
        $this->assertStringContainsString($checkboxcheck,$statuscheck,$missing);
    }

    public function test_ProcessSendBatchMail()
    {
        global $_POST;
        $messages = new MessageSet();
        $messages->loadAll();
        $this->assertSame(2,$messages->getCount(),"Incorrect number of messages in outbox before sending");
        $_POST["message"] = "Hello world this is a test";
        $_POST["source"] = "package";
        $_POST["source_id"] = 1;
        $_POST["max_avatars"] = 1;
        $_POST["avatarids"] = [1];
        $sendHandler = new Send();
        $sendHandler->process();
        $statuscheck = $sendHandler->getOutputObject();
        $this->assertStringContainsString("Sent to 1 avatars",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $messages = new MessageSet();
        $messages->loadAll();
        $this->assertSame(4,$messages->getCount(),"Incorrect number of messages in outbox");
    }
}