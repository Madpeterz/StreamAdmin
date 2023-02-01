<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Server\Create as ServerCreate;
use App\Endpoint\Control\Server\Remove as ServerRemove;
use App\Endpoint\Control\Server\Update;
use App\Endpoint\View\Server\Create;
use App\Endpoint\View\Server\DefaultView;
use App\Endpoint\View\Server\Manage;
use App\Models\Server;
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
        global $testsystem;
        $server = new Server();
        $status = $server->loadByDomain("MagicServerTest");
        $this->assertSame(true,$status->status,"Unable to load test server");
        $testsystem->setPage($server->getId());

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing server manage form element";
        $this->assertStringContainsString("MagicServerTest",$statuscheck,$missing);
        $this->assertStringContainsString("http://supernotused.com",$statuscheck,$missing);
        $this->assertStringContainsString("Basic config",$statuscheck,$missing);
        $this->assertStringContainsString("Domain",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $testsystem, $_POST;
        $server = new Server();
        $status = $server->loadByDomain("MagicServerTest");
        $this->assertSame(true,$status->status,"Unable to load test server");
        $testsystem->setPage($server->getId());


        $manageProcess = new Update();
        $_POST["domain"] = "SuperMagicTest";
        $_POST["controlPanelURL"] = "http://supernotused.com";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Server updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveProcess()
    {
        global $testsystem, $_POST;
        $server = new Server();
        $status = $server->loadByDomain("SuperMagicTest");
        $this->assertSame("Ok",$status->message,"Unable to load test server");
        $this->assertSame(true,$status->status,"Unable to load test server");
        $testsystem->setPage($server->getId());

        $removeProcess = new ServerRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Server removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
