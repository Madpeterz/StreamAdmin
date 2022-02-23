<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Tree\Update;
use App\Models\Treevender;
use PHPUnit\Framework\TestCase;

class Issue95 extends TestCase
{
    public function test_ManageProcess()
    {
        global $page, $_POST;
        $tree = new Treevender();
        $status = $tree->loadByField("name","Demo");
        $this->assertSame(true,$status,"Unable to load test tree");
        $page = $tree->getId();

        $manageProcess = new Update();
        $_POST["name"] = "UnitUpdatedTreeVendIssue95";
        $_POST["textureWaiting"] = "00000000-0000-0000-0000-000000000000";
        $_POST["textureInuse"] = "00000000-0000-0000-0000-000000000000";
        if(isset($_POST["hideSoldout"]) == true) {
            unset($_POST["hideSoldout"]);
        }
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Treevender updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
       
        $tree = new Treevender();
        $status = $tree->loadByField("name","UnitUpdatedTreeVendIssue95");
        $this->assertSame(true,$status,"Unable to load updated tree");
    }
}

