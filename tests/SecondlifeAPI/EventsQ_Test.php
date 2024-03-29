<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\EventQ\Next;
use App\R7\Set\EventsqSet;
use PHPUnit\Framework\TestCase;

class SecondlifeApiEventsQserver extends TestCase
{
    public function test_Next()
    {
        global $_POST, $slconfig, $sql;
        $_POST["method"] = "EventQ";
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
        $raw = time()  . implode("",$real) . $slconfig->getSlLinkCode();
        $_POST["hash"] = sha1($raw);

        $EventsqSet = new EventsqSet();
        $this->assertSame(true,$EventsqSet->loadAll()["status"],"Unable to load message set to check workspace");
        $this->assertSame(3,$EventsqSet->getCount(),"Incorrect number of messages in the Q");

        $Next = new Next();
        $this->assertSame("Not processed",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("hasmessage"),"No message detected but I was expecting one");
        $this->assertSame("RentalStart",$Next->getOutputObject()->getSwapTagString("eventName"),"Incorrect eventname");
        $this->assertStringStartsWith('{"package":"UnitTestPackage","uuid":"499c3e36-69b3-40e5-9229-0cfa5db30766","name":"Test Buyer"',
            $Next->getOutputObject()->getSwapTagString("eventMessage"),"incorrect eventmessage");
        $sql->sqlSave();
        $EventsqSet = new EventsqSet();
        $this->assertSame(true,$EventsqSet->loadAll()["status"],"Unable to load message set to check workspace");
        $this->assertSame(2,$EventsqSet->getCount(),"Incorrect number of messages in the Q");

    }
}
