<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Texturepack\GetPack;
use PHPUnit\Framework\TestCase;

class SecondlifeApiTexturepack extends TestCase
{
    public function test_Getpack()
    {
        global $_POST, $system;
        $this->assertSame(1,$system->getSlConfig()->getId(1),"config not loaded");
        $system->forceProcessURI("Texturepack/Getpack");
        $_POST["version"] = "2.0.0.0";
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "Madpeter Zond";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
        $storage = [
            "version",
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
        $raw = time()  . "TexturepackGetpack" . implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $_POST["texturepack"] = 1;
        $GetPack = new Getpack();
        $this->assertSame("ready",$GetPack->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$GetPack->getLoadOk(),"Load ok failed");
        $GetPack->process();
        $this->assertSame("ok",$GetPack->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame("718fdaf8-df99-5c7f-48fb-feb94db12675",$GetPack->getOutputObject()->getSwapTagString("Texture-Offline"),"incorrect reply");
        $this->assertSame(true,$GetPack->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $_POST["texturepack"] = -45;
        $GetPack = new GetPack();
        $GetPack->process();
        $this->assertSame(false,$GetPack->getOutputObject()->getSwapTagBool("status"),"incorrectly marked as ok");
        $this->assertSame("Invaild texturepack id (or non sent)",$GetPack->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $_POST["texturepack"] = 93445;
        $GetPack = new GetPack();
        $GetPack->process();
        $this->assertSame(false,$GetPack->getOutputObject()->getSwapTagBool("status"),"incorrectly marked as ok");
        $this->assertSame("Unable to load texture pack",$GetPack->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $_POST["ownerkey"] = "289f3e36-69b3-40f5-9229-0c6a5d230766";
        $_POST["objectuuid"] = "b36f71ef-b2a5-ff61-025c-81bbc473deb8";
        $_POST["ownername"] = "Madpeter Test";
        $_POST["texturepack"] = 1;
        $real = [];
        foreach($storage as $valuename)
        {
            $real[] = $_POST[$valuename];
        }
        $_POST["unixtime"] = time();
        $raw = time()  . "TexturepackGetpack". implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $GetPack = new GetPack();
        $this->assertSame(true,$GetPack->getLoadOk(),"Load ok failed");
        $GetPack->process();
        $this->assertSame("ok",$GetPack->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame("718fdaf8-df99-5c7f-48fb-feb94db12675",$GetPack->getOutputObject()->getSwapTagString("Texture-Offline"),"incorrect reply");
        $this->assertSame("Reseller mode",$GetPack->getOutputObject()->getSwapTagString("reseller_mode"),"incorrect mode");
    }
}
