<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Bot\Update;
use App\Endpoint\View\Bot\DefaultView;
use App\Models\Avatar;
use Tests\Mytest;

class BotTest extends Mytest
{
    public function test_BotDefault()
    {
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing bot form element";
        $this->assertStringContainsString("Basic",$statuscheck,$missing);
        $this->assertStringContainsString("Actions",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
        $this->assertStringContainsString("Auto inviter",$statuscheck,$missing);
    }
    public function test_BotUpdate()
    {
        global $_POST;
        $avatar = new Avatar();
        $status = $avatar->loadID(1);
        $this->assertSame(true,$status->status,"Unable to load avatar");
        $_POST["avataruid"] = $avatar->getAvatarUid();
        $_POST["secret"] = substr(md5(microtime()."bb"),0,8);
        $_POST["notecards"] = 1;
        $_POST["ims"] = 1;
        $_POST["invites"] = 1;
        $_POST["inviteGroupUUID"] = "01234567-89ab-cdef-0123-456789abcdef";
        $Update = new Update();
        $Update->process();
        $statuscheck = $Update->getOutputObject();
        $this->assertStringContainsString("Changes saved",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}