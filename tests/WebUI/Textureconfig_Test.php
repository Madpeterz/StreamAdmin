<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Textureconfig\Create as TextureConfigCreate;
use App\Endpoint\Control\Textureconfig\Remove;
use App\Endpoint\Control\Textureconfig\Update;
use App\Endpoint\View\Textureconfig\Create;
use App\Endpoint\View\Textureconfig\DefaultView;
use App\Endpoint\View\Textureconfig\Manage;
use App\Models\Textureconfig;
use PHPUnit\Framework\TestCase;

class TextureconfigTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing textureconfig list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("name",$statuscheck,$missing);
        $this->assertStringContainsString("ID",$statuscheck,$missing);
        $this->assertStringContainsString("SA7 defaults",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_CreateForm()
    {
        $createform = new Create();
        $createform->process();
        $statuscheck = $createform->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing textureconfig create form element";
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Request details",$statuscheck,$missing);
        $this->assertStringContainsString("Waiting for owner",$statuscheck,$missing);
        $this->assertStringContainsString("Stock levels",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $createHandler = new TextureConfigCreate();
        $_POST["name"] = "UnitTestTexturePack";
        $_POST["gettingDetails"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["requestDetails"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["offline"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["waitOwner"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["inUse"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["makePayment"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["stockLevels"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["renewHere"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["proxyRenew"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $createHandler->process();
        $statuscheck = $createHandler->getOutputObject();
        $this->assertStringContainsString("Texture pack created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $testsystem;
        $textureconfig = new Textureconfig();
        $status = $textureconfig->loadByName("UnitTestTexturePack");
        $this->assertSame(true,$status->status,"Unable to load test texture pack");
        $testsystem->setPage($textureconfig->getId());

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing textureconfig manage form element";
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Request details",$statuscheck,$missing);
        $this->assertStringContainsString("Waiting for owner",$statuscheck,$missing);
        $this->assertStringContainsString("Stock levels",$statuscheck,$missing);
        $this->assertStringContainsString("289c3ea6-69b3-40c5-9229-0c6a5d230766",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $testsystem, $_POST;
        $textureconfig = new Textureconfig();
        $status = $textureconfig->loadByName("UnitTestTexturePack");
        $this->assertSame(true,$status->status,"Unable to load test texture pack");
        $testsystem->setPage($textureconfig->getId());

        $manageProcess = new Update();
        $_POST["name"] = "UnitTestTexturePack Updated";
        $_POST["gettingDetails"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["requestDetails"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["offline"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["waitOwner"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["inUse"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["makePayment"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["stockLevels"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["renewHere"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $_POST["proxyRenew"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Texture pack updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
       
        $textureconfig = new Textureconfig();
        $status = $textureconfig->loadByName("UnitTestTexturePack Updated");
        $this->assertSame(true,$status->status,"Unable to load updated textureconfig");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveProcess()
    {
        global $testsystem, $_POST;
        $textureconfig = new Textureconfig();
        $status = $textureconfig->loadByName("UnitTestTexturePack Updated");
        $this->assertSame(true,$status->status,"Unable to load test texture pack");
        $testsystem->setPage($textureconfig->getId());

        $removeProcess = new Remove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertSame("Texture pack removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
