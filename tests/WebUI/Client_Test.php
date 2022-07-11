<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Avatar\Create as AvatarCreate;
use App\Endpoint\Control\Client\Create as ClientCreate;
use App\Endpoint\Control\Client\GetNotecard;
use App\Endpoint\Control\Client\Message;
use App\Endpoint\Control\Client\Revoke as ClientRevoke;
use App\Endpoint\Control\Client\Update;
use App\Endpoint\Control\Stream\Create as StreamCreate;
use App\Endpoint\View\Client\Active;
use App\Endpoint\View\Client\Bynoticelevel;
use App\Endpoint\View\Client\Create;
use App\Endpoint\View\Client\DefaultView;
use App\Endpoint\View\Client\Expired;
use App\Endpoint\View\Client\ListMode;
use App\Endpoint\View\Client\Manage;
use App\Endpoint\View\Client\Soon;
use App\Models\Avatar;
use App\Models\Rental;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client list by notice level element";
        $this->assertStringContainsString("7 day notice",$statuscheck,$missing);
        $this->assertStringContainsString("NoticeLevel",$statuscheck,$missing);
        $this->assertStringContainsString("Count",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_SelectNotice()
    {
        global $system;
        $system->setPage(1);
        $default = new Bynoticelevel();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client list by selected notice level element";
        $this->assertStringContainsString("MadpeterUnit ZondTest",$statuscheck,$missing);
        $this->assertStringContainsString("Rental UID",$statuscheck,$missing);
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("8002",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_ListMode()
    {
        $ListMode = new ListMode();
        $ListMode->process();
        $statuscheck = $ListMode->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client listmode element";
        $this->assertStringContainsString("MadpeterUnit ZondTest",$statuscheck,$missing);
        $this->assertStringContainsString("Rental UID",$statuscheck,$missing);
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("8002",$statuscheck,$missing);
    }

    /**
     * @depends test_SelectNotice
     */
    public function test_CreateForm()
    {
        $createForm = new Create();
        $createForm->process();
        $statuscheck = $createForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client create form element";
        $this->assertStringContainsString("Find/Add avatar",$statuscheck,$missing);
        $this->assertStringContainsString("Days remaining",$statuscheck,$missing);
        $this->assertStringContainsString("Stream UID (Or port)",$statuscheck,$missing);
        $this->assertStringContainsString("multiple streams with",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $streamCreateHandler = new StreamCreate();
        $_POST["port"] = 8004;
        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "/live";
        $_POST["adminUsername"] = "UnitTesting";
        $_POST["adminPassword"] = substr(md5(microtime()."a"),0,8);
        $_POST["djPassword"] = substr(md5(microtime()."b"),0,8);
        $_POST["needswork"] = 0;
        $_POST["apiConfigValue1"] = "";
        $_POST["apiConfigValue2"] = "";
        $_POST["apiConfigValue3"] = "";
        $_POST["api_create"] = 0;
        $streamCreateHandler->process();
        $statuscheck = $streamCreateHandler->getOutputObject();
        $this->assertStringContainsString("Stream created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

        $createProcess = new AvatarCreate();
        $_POST["avatarName"] = "ClientTest Avatar";
        $_POST["avatarUUID"] = "289c3ea6-bfbf-40c5-9200-0c6a5d230700";
        $createProcess->process();
        $statuscheck = $createProcess->getOutputObject();
        $this->assertStringContainsString("Avatar created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

        $createProcess = new AvatarCreate();
        $_POST["avatarName"] = "OtherTest Avatar";
        $_POST["avatarUUID"] = "28cc3ca6-bfbf-40c5-9200-0c6a5d230700";
        $createProcess->process();
        $statuscheck = $createProcess->getOutputObject();
        $this->assertStringContainsString("Avatar created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

        $createForm = new ClientCreate();
        $_POST["streamuid"] = 8004;
        $_POST["avataruid"] = "ClientTest Avatar";
        $_POST["daysremaining"] = 24;
        $createForm->process();
        $statuscheck = $createForm->getOutputObject();
        $this->assertStringContainsString("Client created", $statuscheck->getSwapTagString("message"));
        $this->assertSame(true, $statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $system;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","ClientTest Avatar");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $rental = new Rental();
        $status = $rental->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test rental");
        $system->setPage($rental->getRentalUid());

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client manage element";
        $this->assertStringContainsString("Timeleft:",$statuscheck,$missing);
        $this->assertStringContainsString("Client",$statuscheck,$missing);
        $this->assertStringContainsString("Transfer",$statuscheck,$missing);
        $this->assertStringContainsString("Find",$statuscheck,$missing);
        $this->assertStringContainsString("Avatar UID",$statuscheck,$missing);
        $this->assertStringContainsString("Message",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
        $this->assertStringContainsString("Transactions",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $_POST, $system;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","ClientTest Avatar");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $rental = new Rental();
        $status = $rental->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test rental");
        $system->setPage($rental->getRentalUid());
        // update message
        $manageProcess = new Update();
        $_POST["message"] = "Message updated";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Message Updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        // update adjustment
        $rental = new Rental();
        $status = $rental->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test rental");
        $system->setPage($rental->getRentalUid());
        $manageProcess = new Update();
        $_POST["message"] = $rental->getMessage();
        $_POST["adjustment_dir"] = "true";
        $_POST["adjustment_hours"] = "0";
        $_POST["adjustment_days"] = "21";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Adjusted timeleft",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        // update transfer
        $newav = new Avatar();
        $status = $newav->loadByField("avatarName","OtherTest Avatar");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $rental = new Rental();
        $status = $rental->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test rental");
        $old_owner_id = $rental->getAvatarLink();
        $system->setPage($rental->getRentalUid());
        $_POST["adjustment_dir"] = "true";
        $_POST["adjustment_hours"] = "0";
        $_POST["adjustment_days"] = "0";
        $_POST["transfer_avataruid"] = $newav->getAvatarUid();
        $manageProcess = new Update();
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Ownership transfered",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $updatedrental = new Rental();
        $status = $updatedrental->loadID($rental->getId());
        $this->assertSame(true,$status->status,"Unable to load updated rental");
        $this->assertNotSame($old_owner_id,$updatedrental->getAvatarLink(),"Rental did not transfer correctly");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_MessageClientProcess()
    {
        global $_POST, $system;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","OtherTest Avatar");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $rental = new Rental();
        $status = $rental->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test rental");
        $system->setPage($rental->getRentalUid());
        // send message to client
        $sendMessageToClient = new Message();
        $_POST["mail"] = "This is a test it is only a test";
        $sendMessageToClient->process();
        $statuscheck = $sendMessageToClient->getOutputObject();
        $this->assertStringContainsString("Message added to outbox",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_MessageClientProcess
     */
    public function test_GetNotecard()
    {
        global $system;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","OtherTest Avatar");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $rental = new Rental();
        $status = $rental->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test rental");
        $system->setPage($rental->getRentalUid());

        $getNotecard = new GetNotecard();
        $getNotecard->process();
        $statuscheck = $getNotecard->getOutputObject();
        $this->assertStringContainsString("Control panel:",$statuscheck->getSwapTagString("message"));
        $this->assertStringContainsString("port: 8004",$statuscheck->getSwapTagString("message"));
        $this->assertStringContainsString("Expires:",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_GetNotecard
     */
    public function test_RevokeProcess()
    {
        global $system, $_POST;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","OtherTest Avatar");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $rental = new Rental();
        $status = $rental->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test rental");
        $system->setPage($rental->getRentalUid());

        $removeProcess = new ClientRevoke();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Client rental revoked",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    public function test_Active()
    {
        $Active = new Active();
        $Active->process();
        $statuscheck = $Active->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client list active status element";
        $this->assertStringContainsString("MadpeterUnit ZondTest",$statuscheck,$missing);
        $this->assertStringContainsString("8002",$statuscheck,$missing);
        $this->assertStringContainsString("Timeleft",$statuscheck,$missing);
        $this->assertStringContainsString("Status",$statuscheck,$missing);
        $this->assertStringContainsString("Renewals",$statuscheck,$missing);
    }

    public function test_Soon()
    {
        $Soon = new Soon();
        $Soon->process();
        $statuscheck = $Soon->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client list soon status element";
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("Rental UID",$statuscheck,$missing);
        $this->assertStringContainsString("Avatar",$statuscheck,$missing);
        $this->assertStringContainsString("Renewals",$statuscheck,$missing);
    }

    public function test_Expired()
    {
        $Expired = new Expired();
        $Expired->process();
        $statuscheck = $Expired->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client list expired status element";
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("Rental UID",$statuscheck,$missing);
        $this->assertStringContainsString("Avatar",$statuscheck,$missing);
        $this->assertStringContainsString("Renewals",$statuscheck,$missing);
    }



}
