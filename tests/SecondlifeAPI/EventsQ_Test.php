<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Eventq\Next;
use App\Models\Sets\EventsqSet;
use PHPUnit\Framework\TestCase;

class SecondlifeApiEventsQserver extends TestCase
{
    public function test_Next()
    {
        global $_POST, $testsystem;
        $testsystem->forceProcessURI("EventQ/Next");
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
        $raw = time()  . "EventQNext". implode("",$real) . $testsystem->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);

        $EventsqSet = new EventsqSet();
        $this->assertSame(true,$EventsqSet->loadAll()->status,"Unable to load message set to check workspace");
        $this->assertSame(3,$EventsqSet->getCount(),"Incorrect number of messages in the Q");

        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame("RentalStart",$Next->getOutputObject()->getSwapTagString("eventName"),"Incorrect eventname");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("hasmessage"),"No message detected but I was expecting one");
        $this->assertStringStartsWith('{"package":"UnitTestPackage","uuid":"499c3e36-69b3-40e5-9229-0cfa5db30766","name":"Test Buyer"',
            $Next->getOutputObject()->getSwapTagString("eventMessage"),"incorrect eventmessage");
        $testsystem->getSQL()->sqlSave();
        $EventsqSet = new EventsqSet();
        $this->assertSame(true,$EventsqSet->loadAll()->status,"Unable to load message set to check workspace");
        $this->assertSame(2,$EventsqSet->getCount(),"Incorrect number of messages in the Q");

    }
}
