<?php

namespace StreamAdminR7;

use App\Models\Slconfig;
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
        $this->assertSame(true,array_key_exists("Texture-Offline",$json_obj),"Texture-Offline missing from output: ".$this->getActualOutputForAssertion());
        $this->assertSame(true,array_key_exists("status",$json_obj),"status missing from output: ".$this->getActualOutputForAssertion());

        $this->assertSame("ok",$json_obj["message"],"incorrect reply");
        $this->assertSame("718fdaf8-df99-5c7f-48fb-feb94db12675",$json_obj["Texture-Offline"],"incorrect reply");
        $this->assertSame("1",$json_obj["status"],"marked as failed");

    }

    protected function SetupPost()
    {
        global $_POST, $system;
        $system->forceProcessURI("Texturepack/Getpack");
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "Madpeter Zond";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
        $storage = [
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
        $raw = time()  ."TexturepackGetpack". implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $_POST["texturepack"] = 1;
    }
}