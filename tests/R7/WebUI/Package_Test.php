<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Package\Create as PackageCreate;
use App\Endpoint\Control\Package\Remove as PackageRemove;
use App\Endpoint\Control\Package\Update;
use App\Endpoint\View\Package\Create;
use App\Endpoint\View\Package\DefaultView;
use App\Endpoint\View\Package\Manage;
use App\Endpoint\View\Package\Remove;
use App\Models\Package;
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing package list element";
        $this->assertStringContainsString("UnitTestPackage",$statuscheck,$missing);
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("128",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_CreateForm()
    {
        $createForm = new Create();
        $createForm->process();
        $statuscheck = $createForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing package create element";
        $this->assertStringContainsString("Basics",$statuscheck,$missing);
        $this->assertStringContainsString("Terms",$statuscheck,$missing);
        $this->assertStringContainsString("Auto DJ",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $PackageCreateHandler = new PackageCreate();
        $_POST["name"] = "AlsoUnitTestPackage";
        $_POST["templateLink"] = 1;
        $_POST["cost"] = 66;
        $_POST["days"] = 5;
        $_POST["bitrate"] = 56;
        $_POST["listeners"] = 10;
        $_POST["textureSoldout"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSmall"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSelected"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["autodj"] = false;
        $_POST["autodjSize"] = 0;
        $_POST["apiTemplate"] = "None";
        $_POST["servertypeLink"] = 1;
        $PackageCreateHandler->process();
        $statuscheck = $PackageCreateHandler->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertStringContainsString("Package created",$statuscheck->getSwapTagString("message"));
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $page;
        $package = new Package();
        $status = $package->loadByField("name","AlsoUnitTestPackage");
        $this->assertSame(true,$status,"Unable to load test package");
        $page = $package->getPackageUid();

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing package manage element";
        $this->assertStringContainsString("Terms",$statuscheck,$missing);
        $this->assertStringContainsString("Auto DJ",$statuscheck,$missing);
        $this->assertStringContainsString("Textures",$statuscheck,$missing);
        $this->assertStringContainsString("AlsoUnitTestPackage",$statuscheck,$missing);
        $this->assertStringContainsString("None",$statuscheck,$missing);
        $this->assertStringContainsString("289c3e36-69b3-40c5-9229-0c6a5d230766",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $_POST, $page;
        $package = new Package();
        $status = $package->loadByField("name","AlsoUnitTestPackage");
        $this->assertSame(true,$status,"Unable to load test package");
        $page = $package->getPackageUid();

        $manageProcess = new Update();
        $_POST["name"] = "AlsoUnitTestPackage Updated";
        $_POST["templateLink"] = 1;
        $_POST["cost"] = 777;
        $_POST["days"] = 31;
        $_POST["bitrate"] = 128;
        $_POST["listeners"] = 50;
        $_POST["textureSoldout"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSmall"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSelected"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["autodj"] = false;
        $_POST["autodjSize"] = 0;
        $_POST["apiTemplate"] = "None";
        $_POST["servertypeLink"] = 1;
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Package updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

        $package = new Package();
        $status = $package->loadByField("name","AlsoUnitTestPackage Updated");
        $this->assertSame(true,$status,"Unable to load test package with updated name");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveForm()
    {
        global $page;
        $package = new Package();
        $status = $package->loadByField("name","AlsoUnitTestPackage Updated");
        $this->assertSame(true,$status,"Unable to load test package");
        $page = $package->getPackageUid();

        $removeForm = new Remove();
        $removeForm->process();
        $statuscheck = $removeForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing package remove form element";
        $this->assertStringContainsString("If the package is currenly in use this will fail",$statuscheck,$missing);
        $this->assertStringContainsString("Accept",$statuscheck,$missing);
        $this->assertStringContainsString('<input type="radio" value="Nevermind" name="accept" autocomplete="off" checked',$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveForm
     */
    public function test_RemoveProcess()
    {
        global $page, $_POST;
        $package = new Package();
        $status = $package->loadByField("name","AlsoUnitTestPackage Updated");
        $this->assertSame(true,$status,"Unable to load test package");
        $page = $package->getPackageUid();

        $removeProcess = new PackageRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Package removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
