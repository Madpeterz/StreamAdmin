<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Outbox\Send;
use App\Endpoint\View\Outbox\Bulk;
use App\Endpoint\View\Outbox\DefaultView;
use App\Endpoint\View\Outbox\Details;
use App\Endpoint\View\Outbox\Mail;
use App\Endpoint\View\Outbox\Notecard;
use App\Models\Sets\BotcommandqSet;
use App\Models\Sets\MessageSet;
use Tests\Mytest;

class OutboxText extends Mytest
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $missing = "Missing outbox element";
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Status", $statuscheck, $missing);
        $this->assertStringContainsString("Pending", $statuscheck, $missing);
        $this->assertStringContainsString("object is running normaly", $statuscheck, $missing);
        $this->assertStringContainsString("Package", $statuscheck, $missing);
        $this->assertStringContainsString("Server", $statuscheck, $missing);
        $this->assertStringContainsString("Notice", $statuscheck, $missing);
        $this->assertStringContainsString("Swaps", $statuscheck, $missing);
        $this->assertStringContainsString("[[PACKAGE_AUTODJ]]", $statuscheck, $missing);
        $this->assertStringContainsString("Select avatars", $statuscheck, $missing);
    }

    public function test_OutboxNotecard()
    {
        $notecards = new Notecard();
        $notecards->process();
        $missing = "Missing outbox element";
        $statuscheck = $notecards->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Avatar name", $statuscheck, $missing);
    }

    public function test_OutboxDetails()
    {
        $details = new Details();
        $details->process();
        $missing = "Missing outbox element";
        $statuscheck = $details->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Avatar name", $statuscheck, $missing);
    }

    public function test_OutboxMail()
    {
        $details = new Mail();
        $details->process();
        $missing = "Missing outbox element";
        $statuscheck = $details->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Avatar name", $statuscheck, $missing);
        $this->assertStringContainsString("Start of message", $statuscheck, $missing);
    }

    public function test_BulkSendToPackageForm()
    {
        global $_GET, $system;

        $system->setPage("Package");
        $_GET["packageLink"] = 1;
        $_GET["messagePackage"] = "Hello world this is a test";

        $messagecheck = 'name="message" id="message" value="Hello world this is a test"';
        $sourcecheck = 'id="source" value="Package"';
        $sourcevaluecheck = 'id="source_id" value="1"';
        $avatarcheck = 'id="max_avatars" value="1"';
        $checkboxcheck = 'id="avatarmail1" name="avatarids[]" value="1"';

        $bulkPackage = new Bulk();
        $bulkPackage->process();
        $statuscheck = $bulkPackage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Name", $statuscheck, "Failed check: Name col is missing");
        $this->assertStringContainsString("X", $statuscheck, "Failed check: X col is missing");
        $this->assertStringContainsString("Send to selected", $statuscheck, "Failed check: Send to selected text is missing");
        $this->assertStringContainsString($messagecheck, $statuscheck, "Failed check: message is hidden input");
        $this->assertStringContainsString($sourcecheck, $statuscheck, "Failed check: Name field is listed");
        $this->assertStringContainsString($sourcevaluecheck, $statuscheck, "Failed check: Source field is listed");
        $this->assertStringContainsString($avatarcheck, $statuscheck, "Failed check: max avatar counter");
        $this->assertStringContainsString($checkboxcheck, $statuscheck, "Failed check: Avatar ids are missing");
    }

    public function test_BulkSendToServerForm()
    {
        global $_GET, $system;
        $system->setPage("Server");
        $_GET["serverLink"] = 1;
        $_GET["messageServer"] = "Hello world this is a test";

        $messagecheck = 'id="message" value="' . $_GET["messageServer"] . '"';
        $sourcecheck = 'id="source" value="Server"';
        $sourcevaluecheck = 'id="source_id" value="1"';
        $avatarcheck = 'id="max_avatars" value="1"';
        $checkboxcheck = 'id="avatarmail1" name="avatarids[]" value="1"';

        $bulkPackage = new Bulk();
        $bulkPackage->process();
        $missing = "Missing outbox bulk server element";
        $statuscheck = $bulkPackage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Name", $statuscheck, $missing);
        $this->assertStringContainsString("X", $statuscheck, $missing);
        $this->assertStringContainsString("Send to selected", $statuscheck, $missing);
        $this->assertStringContainsString($messagecheck, $statuscheck, $missing);
        $this->assertStringContainsString($sourcecheck, $statuscheck, $missing);
        $this->assertStringContainsString($sourcevaluecheck, $statuscheck, $missing);
        $this->assertStringContainsString($avatarcheck, $statuscheck, $missing);
        $this->assertStringContainsString($checkboxcheck, $statuscheck, $missing);
    }

    public function test_BulkSendToNoticeForm()
    {
        global $_GET, $system;
        $system->setPage("Notice");
        $_GET["noticeLink"] = 10;
        $_GET["messageStatus"] = "Hello world this is a test";

        $messagecheck = 'id="message" value="' . $_GET["messageStatus"] . '"';
        $sourcecheck = 'id="source" value="Notice"';
        $sourcevaluecheck = 'id="source_id" value="10"';
        $avatarcheck = 'id="max_avatars" value="1"';
        $checkboxcheck = 'id="avatarmail1" name="avatarids[]" value="1"';

        $bulkPackage = new Bulk();
        $bulkPackage->process();
        $missing = "Missing outbox bulk server element";
        $statuscheck = $bulkPackage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Name", $statuscheck, $missing);
        $this->assertStringContainsString("X", $statuscheck, $missing);
        $this->assertStringContainsString("Send to selected", $statuscheck, $missing);
        $this->assertStringContainsString($messagecheck, $statuscheck, $missing);
        $this->assertStringContainsString($sourcecheck, $statuscheck, $missing);
        $this->assertStringContainsString($sourcevaluecheck, $statuscheck, $missing);
        $this->assertStringContainsString($avatarcheck, $statuscheck, $missing);
        $this->assertStringContainsString($checkboxcheck, $statuscheck, $missing);
    }

    public function test_ProcessSendBatchMail()
    {
        global $_POST;
        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(1, $botmessageQ->getCount(), "Incorrect number of messages in bot command Q before sending");
        $messages = new MessageSet();
        $messages->loadAll();
        $this->assertSame(1, $messages->getCount(), "Incorrect number of messages in outbox before sending");
        $_POST["message"] = "Hello world this is a test";
        $_POST["source"] = "Package";
        $_POST["source_id"] = 1;
        $_POST["max_avatars"] = 1;
        $_POST["avatarids"] = [1];
        $sendHandler = new Send();
        $sendHandler->process();
        $statuscheck = $sendHandler->getOutputObject();
        $this->assertStringContainsString("Sent to 1 avatars", $statuscheck->getSwapTagString("message"));
        $this->assertSame(true, $statuscheck->getSwapTagBool("status"), "Status check failed");
        $messages = new MessageSet();
        $messages->loadAll();
        $this->assertSame(1, $messages->getCount(), "Incorrect number of messages in outbox after sending");
        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(2, $botmessageQ->getCount(), "Incorrect number of messages in bot command Q after sending");
    }
}
