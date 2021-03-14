<?php

namespace StreamAdminR7;

use App\R7\Model\Slconfig;
use App\Switchboard\Sys;
use PHPUnit\Framework\TestCase;

class Sys_Test extends TestCase
{
    public function test_SysSwitchboard()
    {
        $this->SetupPost();
        new Sys();
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"status missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("nowork",$json_obj["message"],"incorrect reply");
        $this->assertSame(true,$json_obj["status"],"marked as failed");
    }

    public function test_SysSwitchboardViaPublichtml()
    {
        $this->SetupPost();
        include "src/public_html/sys.php";
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"status missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("nowork",$json_obj["message"],"incorrect reply");
        $this->assertSame(true,$json_obj["status"],"marked as failed");
    }

    protected function SetupPost()
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Notecard";
        $_POST["action"] = "Next";
        $_POST["mode"] = "test";
        $storage = [
            "method",
            "action",
            "mode",
        ];
        $real = [];
        foreach($storage as $valuename)
        {
            $real[] = $_POST[$valuename];
        }
        $_POST["unixtime"] = time();
        $raw = time()  . implode("",$real) . $slconfig->getHttpInboundSecret();
        $_POST["hash"] = sha1($raw);
    }
}