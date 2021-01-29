<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Stream\Create as StreamCreate;
use App\Endpoint\Control\Stream\Remove as StreamRemove;
use App\Endpoint\Control\Stream\Update;
use App\Endpoint\View\Stream\Create;
use App\Endpoint\View\Stream\DefaultView;
use App\Endpoint\View\Stream\Inpackage;
use App\Endpoint\View\Stream\Manage;
use App\Endpoint\View\Stream\Remove;
use App\Models\Package;
use App\Models\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function test_Default()
    {
        $StreamPackageSelect = new DefaultView();
        $StreamPackageSelect->process();
        $statuscheck = $StreamPackageSelect->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing streams packages list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Sold",$statuscheck,$missing);
        $this->assertStringContainsString("Ready",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_ViewSelectedPackage()
    {
        global $page;
        $package = new Package();
        $status = $package->loadID(1);
        $this->assertSame(true,$status,"Unable to load test package");

        $page = $package->getPackageUid();
        $view = new Inpackage();
        $view->process();
        $statuscheck = $view->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing streams list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("UID",$statuscheck,$missing);
        $this->assertStringContainsString("Server",$statuscheck,$missing);
        $this->assertStringContainsString("Port",$statuscheck,$missing);
        $this->assertStringContainsString("Status",$statuscheck,$missing);
        $this->assertStringContainsString("8002",$statuscheck,$missing);
        $this->assertStringContainsString("Sold",$statuscheck,$missing);
    }

    /**
     * @depends test_ViewSelectedPackage
     */
    public function test_CreateForm()
    {
        $StreamCreateForm = new Create();
        $StreamCreateForm->process();
        $statuscheck = $StreamCreateForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream create form element";
        $this->assertStringContainsString("Basics",$statuscheck,$missing);
        $this->assertStringContainsString("Config",$statuscheck,$missing);
        $this->assertStringContainsString("API",$statuscheck,$missing);
        $this->assertStringContainsString("Magic",$statuscheck,$missing);
        $this->assertStringContainsString("API UID 1 & 2",$statuscheck,$missing);
        $this->assertStringContainsString("Note:",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $streamCreateHandler = new StreamCreate();
        $_POST["port"] = 8006;
        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "/live";
        $_POST["adminUsername"] = "MoreUnitTesting";
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
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $page;
        $stream = new Stream();
        $status = $stream->loadByField("port",8006);
        $this->assertSame(true,$status,"Unable to load test stream");
        $page = $stream->getStreamUid();

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream manage form element";
        $this->assertStringContainsString("Basics",$statuscheck,$missing);
        $this->assertStringContainsString("Config",$statuscheck,$missing);
        $this->assertStringContainsString("API UID 1",$statuscheck,$missing);
        $this->assertStringContainsString("Magic",$statuscheck,$missing);
        $this->assertStringContainsString("Centova: Not used",$statuscheck,$missing);
        $this->assertStringContainsString("Azuracast shit gets weird",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
        $this->assertStringContainsString("8006",$statuscheck,$missing);
        $this->assertStringContainsString("MoreUnitTesting",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $page, $_POST;
        $stream = new Stream();
        $status = $stream->loadByField("port",8006);
        $this->assertSame(true,$status,"Unable to load test stream");
        $page = $stream->getStreamUid();

        $manageProcess = new Update();
        $_POST["port"] = 8080;
        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "/live";
        $_POST["originalAdminUsername"] = "MoreUnitTesting";
        $_POST["adminUsername"] = "MoreUnitTesting";
        $_POST["adminPassword"] = substr(md5(microtime()."a"),0,8);
        $_POST["djPassword"] = substr(md5(microtime()."b"),0,8);
        $_POST["needswork"] = 0;
        $_POST["apiConfigValue1"] = "";
        $_POST["apiConfigValue2"] = "";
        $_POST["apiConfigValue3"] = "";
        $_POST["api_create"] = 0;
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Stream updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
       
        $stream = new Stream();
        $status = $stream->loadByField("port",8080);
        $this->assertSame(true,$status,"Unable to load updated stream");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveForm()
    {
        global $page;
        $stream = new Stream();
        $status = $stream->loadByField("port",8080);
        $this->assertSame(true,$status,"Unable to load test stream");
        $page = $stream->getStreamUid();

        $removeForm = new Remove();
        $removeForm->process();
        $statuscheck = $removeForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream remove form element";
        $this->assertStringContainsString("If the stream is currenly in use this will fail",$statuscheck,$missing);
        $this->assertStringContainsString("Accept",$statuscheck,$missing);
        $this->assertStringContainsString('<input type="radio" value="Nevermind" name="accept" autocomplete="off" checked',$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveForm
     */
    public function test_RemoveProcess()
    {
        global $page, $_POST;
        $stream = new Stream();
        $status = $stream->loadByField("port",8080);
        $this->assertSame(true,$status,"Unable to load test stream");
        $page = $stream->getStreamUid();

        $removeProcess = new StreamRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Stream removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
