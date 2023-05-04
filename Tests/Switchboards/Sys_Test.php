<?php

namespace StreamAdminR7;

use App\Switchboard\Sys;
use Tests\Mytest;

class Sys_Test extends Mytest
{
    protected string $baked = "";
    public function test_SysSwitchboard()
    {
        $raw = $this->SetupPost();
        new Sys();
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion()." Raw:".$raw);
        $this->assertSame(true,array_key_exists("status",$json_obj),"status missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("nowork",$json_obj["message"],"incorrect reply: ".$this->baked);
        $this->assertSame(true,$json_obj["status"],"marked as failed");
    }

    protected function SetupPost()
    {
        global $system, $_POST;
        $system->forceProcessURI("Notecard/Next");
        $unixtime = time();
        $_POST["unixtime"] = $unixtime;
        $bits = [$unixtime,"Notecard","Next",$system->getSlConfig()->getHttpInboundSecret()];
        $raw = implode("", $bits);
        $_POST["hash"] = sha1($raw);
        return $raw;
    }
}