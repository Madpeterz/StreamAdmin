<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Object\Ping;
use PHPUnit\Framework\TestCase;

class SecondlifeApiObject extends TestCase
{
    public function test_Ping()
    {
        global $_POST, $system;
        $system->forceProcessURI("Object/Ping");
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
        $raw = time()  ."ObjectPing". implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $ping = new Ping();
        $this->assertSame("ready",$ping->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$ping->getLoadOk(),"Load ok failed");
        $ping->process();
        $this->assertSame("pong",$ping->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$ping->getOutputObject()->getSwapTagBool("status"),"marked as failed");
    }
}
