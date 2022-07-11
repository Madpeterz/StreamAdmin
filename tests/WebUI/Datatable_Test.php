<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Datatables\Update;
use App\Endpoint\View\Datatables\DefaultView;
use App\Endpoint\View\Datatables\Manage;
use PHPUnit\Framework\TestCase;

class DatatableTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing datatable list element";
        $this->assertStringContainsString("Config name",$statuscheck,$missing);
        $this->assertStringContainsString("Stream / List",$statuscheck,$missing);
        $this->assertStringContainsString("manage/2",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_ManageForm()
    {
        global $system;
        $system->setPage(2);
        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing datatable manage element";
        $this->assertStringContainsString("Sort by col",$statuscheck,$missing);
        $this->assertStringContainsString("Direction",$statuscheck,$missing);
        $this->assertStringContainsString("datatables/update/2",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $_POST, $system;
        $system->setPage(2);
        $manageProcess = new Update();
        $_POST["col"] = 5;
        $_POST["dir"] = "asc";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Datatable config updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
