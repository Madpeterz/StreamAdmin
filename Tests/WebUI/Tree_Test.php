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
use App\Models\Treevender;
use App\Models\Treevenderpackages;
use Tests\Mytest;

class TreeVendTest extends Mytest
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
        global $system;
        $tree = new Treevender();
        $status = $tree->loadByName("UnitTestTreeVend");
        $this->assertSame(true,$status->status,"Unable to load test tree");
        $system->setPage($tree->getId());

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing tree manage form element";
        $this->assertStringContainsString("textureWaiting",$statuscheck,$missing);
        $this->assertStringContainsString("textureInuse",$statuscheck,$missing);
        $this->assertStringContainsString("hideSoldout",$statuscheck,$missing);
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
        global $system, $_POST;
        $tree = new Treevender();
        $status = $tree->loadByName("UnitTestTreeVend");
        $this->assertSame(true,$status->status,"Unable to load test tree");
        $system->setPage($tree->getId());

        $manageProcess = new Update();
        $_POST["name"] = "UnitUpdatedTreeVend";
        $_POST["textureWaiting"] = "00000000-0000-0000-0000-000000000000";
        $_POST["textureInuse"] = "00000000-0000-0000-0000-000000000000";
        $_POST["hideSoldout"] = 1;
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Treevender updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
       
        $tree = new Treevender();
        $status = $tree->loadByName("UnitUpdatedTreeVend");
        $this->assertSame(true,$status->status,"Unable to load updated tree");
    }
    /**
     * @depends test_ManageProcess
     */
    public function test_AddPackage()
    {
        global $system, $_POST;
        $tree = new Treevender();
        $status = $tree->loadByName("UnitUpdatedTreeVend");
        $this->assertSame(true,$status->status,"Unable to load test tree");
        $system->setPage($tree->getId());

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
        global $system;
        $treevender_packages = new Treevenderpackages();
        $status = $treevender_packages->loadByField("packageLink",1);
        $this->assertSame(true,$status->status,"Unable to load test treevender package");
        $system->setPage($treevender_packages->getId());

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
        global $system, $_POST;
        $treevender_packages = new Treevenderpackages();
        $status = $treevender_packages->loadByField("packageLink",1);
        $this->assertSame(true,$status->status,"Unable to load test treevender package");
        $system->setPage($treevender_packages->getId());

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
    public function test_RemoveProcess()
    {
        global $system, $_POST;
        $tree = new Treevender();
        $status = $tree->loadByName("UnitUpdatedTreeVend");
        $this->assertSame(true,$status->status,"Unable to load test tree");
        $system->setPage($tree->getId());

        $removeProcess = new TreeRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Tree vender removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
