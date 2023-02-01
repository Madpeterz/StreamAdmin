<?php

namespace StreamAdminR7;

use App\Switchboard\Sys;
use PHPUnit\Framework\TestCase;

class Sys_Test extends TestCase
{
    protected string $baked = "";
    public function test_SysSwitchboard()
    {
        $this->SetupPost();
        new Sys();
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"status missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("nowork",$json_obj["message"],"incorrect reply: ".$this->baked);
        $this->assertSame(true,$json_obj["status"],"marked as failed");
    }

    protected function SetupPost()
    {
        global $testsystem;
        $testsystem->forceProcessURI("Notecard/Next");
        $unixtime = time();
        $_POST["unixtime"] = $unixtime;
        $this->baked = $unixtime  . "NotecardNext". $testsystem->getSlConfig()->getHttpInboundSecret();
        $_POST["hash"] = sha1($this->baked);
    }
}