<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Avatar\Create as AvatarCreate;
use App\Endpoint\Control\Staff\Create as StaffCreate;
use App\Endpoint\Control\Staff\Remove as StaffRemove;
use App\Endpoint\Control\Staff\Update;
use App\Endpoint\View\Staff\Create;
use App\Endpoint\View\Staff\DefaultView;
use App\Endpoint\View\Staff\Manage;
use App\Endpoint\View\Staff\Remove;
use App\Models\Avatar;
use App\Models\Staff;
use PHPUnit\Framework\TestCase;

class StaffTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing staff list element";
        $this->assertStringContainsString("Username",$statuscheck,$missing);
        $this->assertStringContainsString("Madpeter",$statuscheck,$missing);
        $this->assertStringContainsString("Yes",$statuscheck,$missing);
        $this->assertStringContainsString("Owner",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_CreateForm()
    {
        $createForm = new Create();
        $createForm->process();
        $statuscheck = $createForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing staff create form element";
        $this->assertStringContainsString("Avatar UID",$statuscheck,$missing);
        $this->assertStringContainsString("Username",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $createProcess = new AvatarCreate();
        $_POST["avatarName"] = "StaffTest Avatar";
        $_POST["avatarUUID"] = "11113ea6-69b3-40c5-9229-0c6a5d230711";
        $createProcess->process();
        $statuscheck = $createProcess->getOutputObject();
        $this->assertStringContainsString("Avatar created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","StaffTest Avatar");
        $this->assertSame(true,$status->status,"Unable to find avatar to assign to staff");

        $createProcess = new StaffCreate();
        $_POST["avataruid"] = $avatar->getAvatarUid();
        $_POST["username"] = "Unittest";
        $createProcess->process();
        $statuscheck = $createProcess->getOutputObject();
        $this->assertSame("Staff member created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

        $staff = new Staff();
        $status = $staff->loadByField("username","Unittest");
        $this->assertSame(true,$status->status,"Unable to load created staff member");
        $this->assertSame(false,$staff->getOwnerLevel(),"Incorrect owner level applyed");

    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $system;
        $staff = new Staff();
        $status = $staff->loadByField("username","Unittest");
        $this->assertSame(true,$status->status,"Unable to load test staff member");
        $system->setPage($staff->getId());

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing staff manage form element";
        $this->assertStringContainsString("Username",$statuscheck,$missing);
        $this->assertStringContainsString("Unittest",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $system, $_POST;
        $staff = new Staff();
        $status = $staff->loadByField("username","Unittest");
        $this->assertSame(true,$status->status,"Unable to load test staff member");
        $system->setPage($staff->getId());

        $manageProcess = new Update();
        $_POST["username"] = "UpdatedUsername";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Staff member updated passwords reset",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveProcess()
    {
        global $system, $_POST;
        $staff = new Staff();
        $status = $staff->loadByField("username","UpdatedUsername");
        $this->assertSame(true,$status->status,"Unable to load test staff member");
        $system->setPage($staff->getId());

        $removeProcess = new StaffRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Staff member removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
