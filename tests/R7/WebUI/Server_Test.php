<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Server\Create as ServerCreate;
use App\Endpoint\Control\Server\Remove as ServerRemove;
use App\Endpoint\Control\Server\Update;
use App\Endpoint\View\Server\Create;
use App\Endpoint\View\Server\DefaultView;
use App\Endpoint\View\Server\Manage;
use App\Endpoint\View\Server\Remove;
use App\R7\Model\Server;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    public function test_Default()
    {
        $ServersList = new DefaultView();
        $ServersList->process();
        $statuscheck = $ServersList->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing servers list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("Domain",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_CreateForm()
    {
        $ServerCreateForm = new Create();
        $ServerCreateForm->process();
        $statuscheck = $ServerCreateForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing server create form element";
        $this->assertStringContainsString("Basic config",$statuscheck,$missing);
        $this->assertStringContainsString("Domain",$statuscheck,$missing);
        $this->assertStringContainsString("API / Password",$statuscheck,$missing);
        $this->assertStringContainsString("Opt / Toggle status",$statuscheck,$missing);
        $this->assertStringContainsString("Event / Update stream on server",$statuscheck,$missing);
        $this->assertStringContainsString("Api notes",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $serverCreateHandler = new ServerCreate();
        $_POST["domain"] = "MagicServerTest";
        $_POST["controlPanelURL"] = "http://supernotused.com";
        $_POST["apiLink"] = 1;
        $_POST["apiURL"] = "";
        $_POST["apiUsername"] = "";
        $_POST["apiPassword"] = "";
        $_POST["optPasswordReset"] = 0;
        $_POST["optAutodjNext"] = 0;
        $_POST["optToggleAutodj"] = 0;
        $_POST["eventEnableStart"] = 0;
        $_POST["eventDisableExpire"] = 0;
        $_POST["eventDisableRevoke"] = 0;
        $_POST["eventResetPasswordRevoke"] = 0;
        $_POST["eventEnableRenew"] = 0;
        $_POST["optToggleStatus"] = 0;
        $_POST["eventStartSyncUsername"] = 0;
        $_POST["apiServerStatus"] = 0;
        $_POST["eventClearDjs"] = 0;
        $_POST["eventRevokeResetUsername"] = 0;
        $_POST["eventRecreateRevoke"] = 0;
        $_POST["apiSyncAccounts"] = 0;
        $_POST["eventCreateStream"] = 0;
        $_POST["eventUpdateStream"] = 0;
        $serverCreateHandler->process();
        $statuscheck = $serverCreateHandler->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertStringContainsString("Server created",$statuscheck->getSwapTagString("message"));
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $page;
        $server = new Server();
        $status = $server->loadByField("domain","MagicServerTest");
        $this->assertSame(true,$status,"Unable to load test server");
        $page = $server->getId();

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing server manage form element";
        $this->assertStringContainsString("MagicServerTest",$statuscheck,$missing);
        $this->assertStringContainsString("http://supernotused.com",$statuscheck,$missing);
        $this->assertStringContainsString("Basic config",$statuscheck,$missing);
        $this->assertStringContainsString("Domain",$statuscheck,$missing);
        $this->assertStringContainsString("API / Password",$statuscheck,$missing);
        $this->assertStringContainsString("Opt / Toggle status",$statuscheck,$missing);
        $this->assertStringContainsString("Event / Update stream on server",$statuscheck,$missing);
        $this->assertStringContainsString("Api notes",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $page, $_POST;
        $server = new Server();
        $status = $server->loadByField("domain","MagicServerTest");
        $this->assertSame(true,$status,"Unable to load test server");
        $page = $server->getId();


        $manageProcess = new Update();
        $_POST["domain"] = "SuperMagicTest";
        $_POST["controlPanelURL"] = "http://supernotused.com";
        $_POST["apiLink"] = 1;
        $_POST["apiURL"] = "";
        $_POST["apiUsername"] = "";
        $_POST["apiPassword"] = "";
        $_POST["optPasswordReset"] = 0;
        $_POST["optAutodjNext"] = 0;
        $_POST["optToggleAutodj"] = 0;
        $_POST["eventEnableStart"] = 0;
        $_POST["eventDisableExpire"] = 0;
        $_POST["eventDisableRevoke"] = 0;
        $_POST["eventResetPasswordRevoke"] = 0;
        $_POST["eventEnableRenew"] = 0;
        $_POST["optToggleStatus"] = 0;
        $_POST["eventStartSyncUsername"] = 0;
        $_POST["apiServerStatus"] = 0;
        $_POST["eventClearDjs"] = 0;
        $_POST["eventRevokeResetUsername"] = 0;
        $_POST["eventRecreateRevoke"] = 0;
        $_POST["apiSyncAccounts"] = 0;
        $_POST["eventCreateStream"] = 0;
        $_POST["eventUpdateStream"] = 0;
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Server updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveForm()
    {
        global $page;
        $server = new Server();
        $status = $server->loadByField("domain","SuperMagicTest");
        $this->assertSame(true,$status,"Unable to load test server");
        $page = $server->getId();

        $removeForm = new Remove();
        $removeForm->process();
        $statuscheck = $removeForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing server remove form element";
        $this->assertStringContainsString("If the server currenly in use this will fail",$statuscheck,$missing);
        $this->assertStringContainsString("Accept",$statuscheck,$missing);
        $this->assertStringContainsString('<input type="radio" value="Nevermind" name="accept" autocomplete="off" checked',$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveForm
     */
    public function test_RemoveProcess()
    {
        global $page, $_POST;
        $server = new Server();
        $status = $server->loadByField("domain","SuperMagicTest");
        $this->assertSame(true,$status,"Unable to load test server");
        $page = $server->getId();

        $removeProcess = new ServerRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Server removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
