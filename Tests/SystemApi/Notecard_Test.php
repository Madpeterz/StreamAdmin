<?php

namespace StreamAdminR7;

use App\Endpoint\SystemApi\Notecard\Next;
use Tests\Mytest;

class SystemApiNotecard extends Mytest
{
    public function test_Next()
    {
        $this->SetupPost("Next");
        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertNotEquals("",$Next->getOutputObject()->getSwapTagString("AvatarUUID"),"AvatarUUID is empty");
        $this->assertSame(36,nullSafeStrLen($Next->getOutputObject()->getSwapTagString("AvatarUUID")),"AvatarUUID is to short");
        $this->assertNotEquals("",$Next->getOutputObject()->getSwapTagString("NotecardTitle"),"NotecardTitle is empty");
        $this->assertNotEquals("",$Next->getOutputObject()->getSwapTagString("NotecardContent"),"NotecardContent is empty");
    }

    protected function SetupPost($action)
    {
        global $_POST, $system;
        $_POST["method"] = "Notecard";
        $_POST["action"] = $action;
        $_POST["unixtime"] = time();
        $bits = [$_POST["unixtime"],$_POST["method"],$_POST["action"],$system->getSlConfig()->getHttpInboundSecret()];
        $_POST["token"] = sha1(implode("", $bits));
    }

}
