<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Bot\NotecardSync;
use App\Models\Sets\BotcommandqSet;
use PHPUnit\Framework\TestCase;

class SecondlifeApiBot extends TestCase
{
    public function test_Notecardsync()
    {
        global $_POST, $system;
        $system->getSlConfig()->setHttpInboundSecret("httpunit");
        $this->assertSame(true,$system->getSlConfig()->updateEntry()->status,"Unable to set HTTP code as needed");
        $this->assertSame(true,$system->getSlConfig()->loadID(1)->status,"Unable to load updated config");
        $system->forceProcessURI("Bot/Notecardsync");
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
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
        $raw = time()  . "BotNotecardsync".implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";

        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(4,$reply,"Current number of events in the Q is not correct"); 

        $Notecardsync = new NotecardSync();
        $this->assertSame("ready",$Notecardsync->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Notecardsync->getLoadOk(),"Load ok failed");
        $Notecardsync->process();
        $this->assertSame("ok",$Notecardsync->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Notecardsync->getOutputObject()->getSwapTagBool("status"),"marked as failed");

        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(5,$reply,"Current number of events in the Q is not correct"); 
    }
}
