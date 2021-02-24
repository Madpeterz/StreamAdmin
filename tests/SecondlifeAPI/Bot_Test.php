<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Bot\Notecardsync;
use PHPUnit\Framework\TestCase;

class SecondlifeApiBot extends TestCase
{
    public function test_Notecardsync()
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Bot";
        $_POST["action"] = "Notecardsync";
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
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


        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $Notecardsync = new Notecardsync();
        $this->assertSame("Not processed",$Notecardsync->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Notecardsync->getLoadOk(),"Load ok failed");
        $Notecardsync->process();
        $this->assertStringStartsWith("FetchNextNotecard",$Notecardsync->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Notecardsync->getOutputObject()->getSwapTagBool("hassyncmessage"),"incorrect sync flag: "
        .$Notecardsync->getOutputObject()->getSwapTagString("message"));
        $this->assertSame(true,$Notecardsync->getOutputObject()->getSwapTagBool("status"),"marked as failed");
    }
}
