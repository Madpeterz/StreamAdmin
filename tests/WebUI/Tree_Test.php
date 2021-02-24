<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Tree\Addpackage;
use App\Endpoint\Control\Tree\Create as TreeCreate;
use App\Endpoint\Control\Tree\Remove as TreeRemove;
use App\Endpoint\Control\Tree\Removepackage as TreeRemovepackage;
use App\Endpoint\Control\Tree\Update;
use App\Endpoint\View\Tree\Create;
use App\Endpoint\View\Tree\DefaultView;
use App\Endpoint\View\Tree\Manage;
use App\Endpoint\View\Tree\Remove;
use App\Endpoint\View\Tree\Removepackage;
use App\R7\Model\Treevender;
use App\R7\Model\Treevenderpackages;
use PHPUnit\Framework\TestCase;

class TreeVendTest extends TestCase
{
    public function test_CreateForm()
    {
        $createform = new Create();
        $createform->process();
        $statuscheck = $createform->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing tree create form element";
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $createHandler = new TreeCreate();
        $_POST["name"] = "UnitTestTreeVend";
        
        $createHandler->process();
        $statuscheck = $createHandler->getOutputObject();
        $this->assertStringContainsString("Tree vender created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing tree list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("ID",$statuscheck,$missing);
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("UnitTestTreeVend",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_ManageForm()
    {
        global $page;
        $tree = new Treevender();
        $status = $tree->loadByField("name","UnitTestTreeVend");
        $this->assertSame(true,$status,"Unable to load test tree");
        $page = $tree->getId();

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing tree manage form element";
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Action",$statuscheck,$missing);
        $this->assertStringContainsString("Package",$statuscheck,$missing);
        $this->assertStringContainsString("Add package",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $page, $_POST;
        $tree = new Treevender();
        $status = $tree->loadByField("name","UnitTestTreeVend");
        $this->assertSame(true,$status,"Unable to load test tree");
        $page = $tree->getId();

        $manageProcess = new Update();
        $_POST["name"] = "UnitUpdatedTreeVend";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Treevender updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
       
        $tree = new Treevender();
        $status = $tree->loadByField("name","UnitUpdatedTreeVend");
        $this->assertSame(true,$status,"Unable to load updated tree");
    }
    /**
     * @depends test_ManageProcess
     */
    public function test_AddPackage()
    {
        global $page, $_POST;
        $tree = new Treevender();
        $status = $tree->loadByField("name","UnitUpdatedTreeVend");
        $this->assertSame(true,$status,"Unable to load test tree");
        $page = $tree->getId();

        $addPackage = new Addpackage();
        $_POST["package"] = 1;
        $addPackage->process();
        $statuscheck = $addPackage->getOutputObject();
        $this->assertStringContainsString("Package added to tree vender",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
    /**
     * @depends test_AddPackage
     */
    public function test_RemovePackageForm()
    {
        global $page;
        $treevender_packages = new Treevenderpackages();
        $status = $treevender_packages->loadByField("packageLink",1);
        $this->assertSame(true,$status,"Unable to load test treevender package");
        $page = $treevender_packages->getId();

        $removePackage = new Removepackage();
        $removePackage->process();
        $statuscheck = $removePackage->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing tree remove package form element";
        $this->assertStringContainsString("Warning",$statuscheck,$missing);
        $this->assertStringContainsString("Accept",$statuscheck,$missing);
        $this->assertStringContainsString('<input type="radio" value="Nevermind" name="accept" autocomplete="off" checked',$statuscheck,$missing);
    }

    /**
     * @depends test_RemovePackageForm
     */
    public function test_RemovePackageProcess()
    {
        global $page, $_POST;
        $treevender_packages = new Treevenderpackages();
        $status = $treevender_packages->loadByField("packageLink",1);
        $this->assertSame(true,$status,"Unable to load test treevender package");
        $page = $treevender_packages->getId();

        $removeProcess = new TreeRemovepackage();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Tree vender linked package removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_RemovePackageProcess
     */
    public function test_RemoveForm()
    {
        global $page;
        $tree = new Treevender();
        $status = $tree->loadByField("name","UnitUpdatedTreeVend");
        $this->assertSame(true,$status,"Unable to load test tree");
        $page = $tree->getId();

        $removeForm = new Remove();
        $removeForm->process();
        $statuscheck = $removeForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing tree package form element";
        $this->assertStringContainsString("Warning",$statuscheck,$missing);
        $this->assertStringContainsString("Accept",$statuscheck,$missing);
        $this->assertStringContainsString('<input type="radio" value="Nevermind" name="accept" autocomplete="off" checked',$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveForm
     */
    public function test_RemoveProcess()
    {
        global $page, $_POST;
        $tree = new Treevender();
        $status = $tree->loadByField("name","UnitUpdatedTreeVend");
        $this->assertSame(true,$status,"Unable to load test tree");
        $page = $tree->getId();

        $removeProcess = new TreeRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Tree vender removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
