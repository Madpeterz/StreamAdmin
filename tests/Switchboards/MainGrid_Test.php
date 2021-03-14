<?php

namespace StreamAdminR7;

use App\R7\Model\Slconfig;
use App\Switchboard\MainGrid as SwitchboardMainGrid;
use PHPUnit\Framework\TestCase;

class MainGrid extends TestCase
{
    public function test_MainGrid()
    {
        $this->SetupPost();
        new SwitchboardMainGrid();
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("texture_offline",$json_obj),"texture_offline missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"status missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("ok",$json_obj["message"],"incorrect reply");
        $this->assertSame("718fdaf8-df99-5c7f-48fb-feb94db12675",$json_obj["texture_offline"],"incorrect reply");
        $this->assertSame("1",$json_obj["status"],"marked as failed");

    }

    public function test_MainGridViaPublichtml()
    {
        $this->SetupPost();
        include "src/public_html/api.php";
        $json_obj = json_decode($this->getActualOutputForAssertion(),true);
        $this->assertSame(true,array_key_exists("message",$json_obj),"Message missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("texture_offline",$json_obj),"texture_offline missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"status missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("ok",$json_obj["message"],"incorrect reply");
        $this->assertSame("718fdaf8-df99-5c7f-48fb-feb94db12675",$json_obj["texture_offline"],"incorrect reply");
        $this->assertSame("1",$json_obj["status"],"marked as failed");
    }

    protected function SetupPost()
    {
        global $_POST, $slconfig;
        if($slconfig == null) {
            $slconfig = new Slconfig();
            $this->assertSame(true,$slconfig->loadID(1),"Unable to load site config");
        }
        $_POST["method"] = "Texturepack";
        $_POST["action"] = "Getpack";
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "Madpeter Zond";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
        $storage = [
            "method",
            "action",
            "mode",
            "objectuuid",
            "regionname",
            "ownerkey",
            "ownername",
            "pos",
            "objectname",
            "objecttype",
        ];
        $real = [];
        foreach($storage as $valuename)
        {
            $real[] = $_POST[$valuename];
        }
        $_POST["unixtime"] = time();
        $raw = time()  . implode("",$real) . $slconfig->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $_POST["texturepack"] = 1;
    }
}