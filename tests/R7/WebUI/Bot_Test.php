<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Bot\Update;
use App\Endpoint\View\Bot\DefaultView;
use App\R7\Model\Avatar;
use PHPUnit\Framework\TestCase;

class BotTest extends TestCase
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
    }
    public function test_BotUpdate()
    {
        global $_POST;
        $avatar = new Avatar();
        $status = $avatar->loadID(1);
        $this->assertSame(true,$status,"Unable to load avatar");
        $_POST["avataruid"] = $avatar->getAvatarUid();
        $_POST["secret"] = substr(md5(microtime()."bb"),0,8);
        $_POST["notecards"] = 0;
        $_POST["ims"] = 0;
        $Update = new Update();
        $Update->process();
        $statuscheck = $Update->getOutputObject();
        $this->assertStringContainsString("Changes saved",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}