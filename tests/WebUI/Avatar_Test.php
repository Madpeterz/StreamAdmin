<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Avatar\Create as AvatarCreate;
use App\Endpoint\Control\Avatar\Finder;
use App\Endpoint\Control\Avatar\Remove as AvatarRemove;
use App\Endpoint\Control\Avatar\Update;
use App\Endpoint\View\Avatar\Create;
use App\Endpoint\View\Avatar\DefaultView;
use App\Endpoint\View\Avatar\Manage;
use App\Endpoint\View\Avatar\Remove;
use App\Models\Avatar as ModelsAvatar;
use PHPUnit\Framework\TestCase;

class AvatarTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing avatar list element";
        $this->assertStringContainsString("Search: Name or UUID",$statuscheck,$missing);
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("MadpeterUnit",$statuscheck,$missing);
        $this->assertStringContainsString("Start",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_CreateForm()
    {
        $createForm = new Create();
        $createForm->process();
        $statuscheck = $createForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing avatar create element";
        $this->assertStringContainsString("SL UUID",$statuscheck,$missing);
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Madpeter Zond",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    /**
     * @depends test_CreateForm
     */
    public function test_CreateProcess()
    {
        global $_POST;
        $createProcess = new AvatarCreate();
        $_POST["avatarName"] = "UnitTest Avatar";
        $_POST["avatarUUID"] = "289c3ea6-69b3-40c5-9229-0c6a5d230766";
        $createProcess->process();
        $statuscheck = $createProcess->getOutputObject();
        $this->assertStringContainsString("Avatar created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_CreateProcess
     */
    public function test_ManageForm()
    {
        global $page, $sql;
        $avatar = new ModelsAvatar();
        $status = $avatar->loadByField("avatarName","UnitTest Avatar");
        $this->assertSame(true,$status,"Unable to load test avatar");
        $page = $avatar->getAvatarUid();
        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing avatar manage element";
        $this->assertStringContainsString("SL UUID",$statuscheck,$missing);
        $this->assertStringContainsString("UnitTest Avatar",$statuscheck,$missing);
        $this->assertStringContainsString("289c3ea6-69b3-40c5-9229-0c6a5d230766",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_ManageForm
     */
    public function test_ManageProcess()
    {
        global $_POST, $page;
        $avatar = new ModelsAvatar();
        $status = $avatar->loadByField("avatarName","UnitTest Avatar");
        $this->assertSame(true,$status,"Unable to load test avatar");
        $page = $avatar->getAvatarUid();
        $manageProcess = new Update();
        $_POST["avatarName"] = "UnitTest Updated";
        $_POST["avatarUUID"] = "289c3ea6-69b3-40c5-9229-000a5d230766";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Avatar updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_ManageProcess
     */
    public function test_FinderResults()
    {
        global $_POST;
        $finder = new Finder();
        $_POST["avatarfind"] = "UnitTest Updated";
        $finder->process();
        $statuscheck = $finder->getOutputObject();
        $this->assertStringContainsString("ok",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $values = $statuscheck->getSwapTagArray("values");
        $this->assertSame(3,count($values),"Incorrect number of results");
        $this->assertSame("UnitTest Updated",$values["matchname"],"Incorrect avatar returned by finder");
    }

    /**
     * @depends test_FinderResults
     */
    public function test_SearchResults()
    {
        global $_GET, $sql;
        $_GET["name"] = "Upda";
        $DefaultView = new DefaultView();
        $DefaultView->process();
        $statuscheck = $DefaultView->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing avatar search element";
        $this->assertStringContainsString("UnitTest Updated",$statuscheck,$missing);
        $this->assertStringContainsString("Name or UUID",$statuscheck,$missing);
        $this->assertStringContainsString("Upda",$statuscheck,$missing);
    }

    /**
     * @depends test_SearchResults
     */
    public function test_RemoveProcess()
    {
        global $page, $_POST;
        $avatar = new ModelsAvatar();
        $status = $avatar->loadByField("avatarName","UnitTest Updated");
        $this->assertSame(true,$status,"Unable to load test avatar");
        $page = $avatar->getAvatarUid();
        $removeProcess = new AvatarRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Avatar removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
