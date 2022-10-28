<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Renew\Costandtime;
use App\Endpoint\Secondlifeapi\Renew\Details;
use App\Endpoint\Secondlifeapi\Renew\Renewnow;
use App\Models\Rental;
use App\Models\Sets\EventsqSet;
use PHPUnit\Framework\TestCase;

class Issue71 extends TestCase
{
    public function test_Issue71currentCountInDB()
    {       
        $EventsqSet = new EventsqSet();
        $reply = $EventsqSet->countInDB();
        $this->assertSame(7,$reply,"Current number of events in the Q is not correct");
    }

    /**
     * @depends test_Issue71currentCountInDB
     */
    public function test_Issue71Renewnow()
    {
        global $_POST;
        $this->setupPost("Renewnow");
        $_POST["avatarUUID"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $Details = new Details();
        $Details->process();
        $outputObj = $Details->getOutputObject();
        $tags = $outputObj->getAllTags();
        $this->assertNotEmpty($outputObj,"output object is empty, something has gone very wrong");
        $dataset = $outputObj->getSwapTagArray("dataset");
        $this->assertNotEmpty($dataset,"Dataset is empty Tags: ".json_encode($tags)." reply object is: ".json_encode($outputObj)."");
        $split = explode("|||",$dataset[0]);
        $this->setupPost("Costandtime");
        $_POST["rentalUid"] = $split[0];
        $rentalOld = new Rental();
        $this->assertSame(true,$rentalOld->loadByRentalUid($split[0])->status,"Unable to load rental before to check changes");
        $this->assertSame(0,$rentalOld->getRenewals(),"Renewals value is not zero as expected");
        $Costandtime = new Costandtime();
        $Costandtime->process();
        $_POST["avatarUUID"] = "c46971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["avatarName"] = "James Pond";
        $_POST["amountpaid"] = 200;
        $Renewnow = new Renewnow();
        $this->assertSame("ready",$Renewnow->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Renewnow->getLoadOk(),"Load ok failed");
        $Renewnow->process();
        $this->assertStringStartsWith(
            "Payment accepted there is now:",
            $Renewnow->getOutputObject()->getSwapTagString("message"),
            "Incorrect message"
        );
        $this->assertSame(true,$Renewnow->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $rentalNew = new Rental();
        $this->assertSame(true,$rentalNew->loadByRentalUid($split[0])->status,"Unable to load rental after to check changes");
        $this->assertSame(4,$rentalNew->getRenewals(),"Renewals value did not change");
    }

    /**
     * @depends test_Issue71Renewnow
     */
    public function test_Issue71reCountInDB()
    {       
        $EventsqSet = new EventsqSet();
        $reply = $EventsqSet->countInDB();
        $this->assertSame(8,$reply,"Current number of events in the Q is not correct");
    }

    /**
     * @depends test_Issue71reCountInDB
     */
    public function test_Issue71checkEventQMessage()
    {       
        $EventsqSet = new EventsqSet();
        $EventsqSet->loadNewest(1);
        $eventQ = $EventsqSet->getFirst();
        $this->assertStringContainsString("viaProxy",$eventQ->getEventMessage(),"viaProxy is missing");
        $this->assertStringContainsString("uuidProxy",$eventQ->getEventMessage(),"uuidProxy is missing");
        $this->assertStringContainsString("nameProxy",$eventQ->getEventMessage(),"nameProxy is missing");
    }

    protected function setupPost(string $target)
    {
        global $_POST, $system;
        $system->forceProcessURI("Renew/".$target);
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
        $raw = time()  ."Renew".$target. implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
    }
}

