<?php

namespace StreamAdminR7;

use App\Endpoint\SystemApi\Notecard\Next;
use PHPUnit\Framework\TestCase;

class SystemApiNotecard extends TestCase
{
    public function test_Next()
    {
        $this->SetupPost("Next");
        $Next = new Next();
        $this->assertSame("Not processed",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertNotEquals("",$Next->getOutputObject()->getSwapTagString("AvatarUUID"),"AvatarUUID is empty");
        $this->assertSame(36,strlen($Next->getOutputObject()->getSwapTagString("AvatarUUID")),"AvatarUUID is to short");
        $this->assertNotEquals("",$Next->getOutputObject()->getSwapTagString("NotecardTitle"),"NotecardTitle is empty");
        $this->assertNotEquals("",$Next->getOutputObject()->getSwapTagString("NotecardContent"),"NotecardContent is empty");
    }

    protected function SetupPost($action)
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Notecard";
        $_POST["action"] = $action;
        $_POST["unixtime"] = time();
        $bits = [$_POST["unixtime"],$_POST["method"],$_POST["action"],$slconfig->getHttpInboundSecret()];
        error_log("test raw:".implode("", $bits));
        $_POST["token"] = sha1(implode("", $bits));
    }

}
