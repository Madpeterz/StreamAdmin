<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Avatar\Create as AvatarCreate;
use App\Endpoint\Control\Banlist\Clear;
use App\Endpoint\Control\Banlist\Create;
use App\Endpoint\View\Banlist\DefaultView;
use App\Models\Avatar;
use App\Models\Banlist;
use PHPUnit\Framework\TestCase;

class BanlistTest extends TestCase
{
    public function test_BanlistDefault()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing avatar list element";
        $this->assertStringContainsString("Search: Name or UUID",$statuscheck,$missing);
        $this->assertStringContainsString("Add to ban list",$statuscheck,$missing);
        $this->assertStringContainsString("Goodbye",$statuscheck,$missing);
    }

    /**
     * @depends test_BanlistDefault
     */
    public function test_BanlistCreateProcess()
    {
        global $_POST;
        $createProcess = new AvatarCreate();
        $_POST["avatarName"] = "Banlist TestAvatar";
        $_POST["avatarUUID"] = "000c3ea6-69b3-40c5-9229-0c6a5d230766";
        $createProcess->process();
        $statuscheck = $createProcess->getOutputObject();
        $this->assertStringContainsString("Avatar created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $avatar = new Avatar();
        $status = $avatar->loadByAvatarName("Banlist TestAvatar");
        $this->assertSame(true,$status->status,"Unable to load test avatar");

        $_POST["uid"] = $avatar->getAvatarUid();
        $createProcess = new Create();
        $createProcess->process();
        $statuscheck = $createProcess->getOutputObject();
        $this->assertStringContainsString("Entry created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_BanlistCreateProcess
     */
    public function test_BanlistCheckDisplayAgain()
    {
        global $_GET;
        unset($_GET["name"]);
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing avatar list element";
        $this->assertStringContainsString("Newest 30 avatars banned",$default->getOutputObject()->getSwapTagString("page_title"),$missing);
        $this->assertStringContainsString("Banlist TestAvatar",$statuscheck,$missing);
    }

    /**
     * @depends test_BanlistCheckDisplayAgain
     */
    public function test_BanlistSearchResults()
    {
        global $_GET;
        $_GET["name"] = "Banli";
        $DefaultView = new DefaultView();
        $DefaultView->process();
        $statuscheck = $DefaultView->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing avatar search element";
        $this->assertStringContainsString("Banlist TestAvatar",$statuscheck,$missing);
    }

    /**
     * @depends test_BanlistSearchResults
     */
    public function test_BanlistRemoveProcess()
    {
        global $testsystem;
        $avatar = new Avatar();
        $status = $avatar->loadByAvatarName("Banlist TestAvatar");
        $this->assertSame(true,$status->status,"Unable to find test avatar");
        $banlist = new Banlist();
        $status = $banlist->loadByAvatarLink($avatar->getId());
        $this->assertSame(true,$status->status,"Unable to find banlist entry");

        $testsystem->setPage($banlist->getId());
        $removeProcess = new Clear();
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Entry removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
