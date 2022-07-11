<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Mailserver\Next;
use App\Models\Sets\BotcommandqSet;
use App\Models\Sets\MessageSet;
use PHPUnit\Framework\TestCase;

class SecondlifeApiMailserver extends TestCase
{
    public function test_Next()
    {
        global $_POST, $system, $sql;
        $_POST["method"] = "Mailserver";
        $_POST["action"] = "Next";
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
        $raw = time()  . implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);

        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(3,$botmessageQ->getCount(),"Incorrect number of messages in bot command Q");
        $messageSet = new MessageSet();
        $this->assertSame(true,$messageSet->loadAll()["status"],"Unable to load message set to check workspace");
        $this->assertSame(5,$messageSet->getCount(),"Incorrect number of messages in the Q");

        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("hasmessage"),"No message detected but I was expecting one");
        $this->assertSame("289c3e36-69b3-40c5-9229-0c6a5d230766",$Next->getOutputObject()->getSwapTagString("avatarUUID"),"Incorrect mail target");
        $this->assertStringStartsWith("Web panel setup finished",$Next->getOutputObject()->getSwapTagString("message"),"incorrect message loaded");
        $sql->sqlSave();

        $messageSet = new MessageSet();
        $this->assertSame(true,$messageSet->loadAll()["status"],"Unable to load message set to check workspace");
        $this->assertSame(4,$messageSet->getCount(),"Incorrect number of messages in the Q");
        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(3,$botmessageQ->getCount(),"Incorrect number of messages in bot command Q [after mailserver processed]");
    }
}
