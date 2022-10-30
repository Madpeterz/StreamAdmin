<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Mailserver\Next;
use App\Models\Sets\BotcommandqSet;
use App\Models\Sets\MessageSet;
use PHPUnit\Framework\TestCase;

class SecondlifeApiMailserver extends TestCase
{
    public function test_Next()
    {
        global $_POST, $system;
        $system->forceProcessURI("Mailserver/Next");
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
$_POST["version"] = "2.0.0.0";

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
        $raw = time()  . "MailserverNext". implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);

        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(3,$botmessageQ->getCount(),"Incorrect number of messages in bot command Q");
        $messageSet = new MessageSet();
        $this->assertSame(true,$messageSet->loadAll()->status,"Unable to load message set to check workspace");
        $this->assertSame(5,$messageSet->getCount(),"Incorrect number of messages in the Q");

        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertStringStartsWith("Web panel setup finished",$Next->getOutputObject()->getSwapTagString("message"),"incorrect message loaded");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("hasmessage"),"No message detected but I was expecting one");
        $this->assertSame("b36971ef-b2a5-f461-025c-81bbc473deb8",$Next->getOutputObject()->getSwapTagString("avatarUUID"),"Incorrect mail target");
        $system->getSQL()->sqlSave();

        $messageSet = new MessageSet();
        $this->assertSame(true,$messageSet->loadAll()->status,"Unable to load message set to check workspace");
        $this->assertSame(4,$messageSet->getCount(),"Incorrect number of messages in the Q");
        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(3,$botmessageQ->getCount(),"Incorrect number of messages in bot command Q [after mailserver processed]");
    }
}
