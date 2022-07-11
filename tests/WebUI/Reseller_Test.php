<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Reseller\Remove as ResellerRemove;
use App\Endpoint\Control\Reseller\Update;
use App\Endpoint\View\Reseller\DefaultView;
use App\Endpoint\View\Reseller\Manage;
use App\Endpoint\View\Reseller\Remove;
use App\Helpers\AvatarHelper;
use App\Helpers\ResellerHelper;
use App\Models\Avatar;
use App\Models\Reseller;
use PHPUnit\Framework\TestCase;

class ResellerTest extends TestCase
{
    public function test_Default()
    {
        $avatarhelper = new AvatarHelper();
        $status = $avatarhelper->loadOrCreate("289c3fa6-69b3-40c5-92f9-0c6a5d2f0766","Reseller Test");
        $this->assertSame(true,$status->status,"Unable to create test avatar");
        $avatar = $avatarhelper->getAvatar();
        $resellerhelper = new ResellerHelper();
        $status = $resellerhelper->loadOrCreate($avatar->getId(),true,44);
        $this->assertSame(true,$status->status,"Unable to create test reseller");
        $reseller = $resellerhelper->getReseller();

        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing reseller list element";
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Allow",$statuscheck,$missing);
        $this->assertStringContainsString("Rate",$statuscheck,$missing);
        $this->assertStringContainsString($reseller->getRate(),$statuscheck,$missing);
        $this->assertStringContainsString("MadpeterUnit ZondTest",$statuscheck,$missing);
        $this->assertStringContainsString("Yes",$statuscheck,$missing);
        $this->assertStringContainsString("automagicly accepted",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_ManageForm()
    {
        global $system;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","Reseller Test");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $system->setPage($avatar->getAvatarUid());
        $reseller = new Reseller();
        $status = $reseller->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test reseller");
        $system->setPage($reseller->getId());

        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing reseller manage form element";
        $this->assertStringContainsString("Allow",$statuscheck,$missing);
        $this->assertStringContainsString("Rate (as %)",$statuscheck,$missing);
        $this->assertStringContainsString($reseller->getRate(),$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $_POST, $system;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","Reseller Test");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $system->setPage($avatar->getAvatarUid());
        $reseller = new Reseller();
        $status = $reseller->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test reseller");
        $system->setPage($reseller->getId());

        $manageProcess = new Update();
        $_POST["rate"] = 15;
        $_POST["allowed"] = 0;
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Reseller updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

        $reseller = new Reseller();
        $status = $reseller->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test reseller");
        $this->assertSame(false,$reseller->getAllowed(),"Allowed status did not update");
        $this->assertSame(15,$reseller->getRate(),"Rate did not update");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_RemoveForm()
    {
        global $system;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","Reseller Test");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $system->setPage($avatar->getAvatarUid());
        $reseller = new Reseller();
        $status = $reseller->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test reseller");
        $system->setPage($reseller->getId());

        $removeForm = new Remove();
        $removeForm->process();
        $statuscheck = $removeForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing reseller remove form element";
        $this->assertStringContainsString("If the reseller is currenly in use this will fail",$statuscheck,$missing);
        $this->assertStringContainsString("Accept",$statuscheck,$missing);
        $this->assertStringContainsString('<input type="radio" value="Nevermind" name="accept" autocomplete="off" checked',$statuscheck,$missing);
        $this->assertStringContainsString("Remove",$statuscheck,$missing);
    }

    /**
     * @depends test_RemoveForm
     */
    public function test_RemoveProcess()
    {
        global $system, $_POST;
        $avatar = new Avatar();
        $status = $avatar->loadByField("avatarName","Reseller Test");
        $this->assertSame(true,$status->status,"Unable to load test avatar");
        $system->setPage($avatar->getAvatarUid());
        $reseller = new Reseller();
        $status = $reseller->loadByField("avatarLink",$avatar->getId());
        $this->assertSame(true,$status->status,"Unable to load test reseller");
        $system->setPage($reseller->getId());

        $removeProcess = new ResellerRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Reseller removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
