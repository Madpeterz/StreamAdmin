<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Renew\Costandtime;
use App\Endpoint\SecondLifeApi\Renew\Details;
use App\Endpoint\SecondLifeApi\Renew\Renewnow;
use App\R7\Model\Rental;
use App\R7\Set\EventsqSet;
use PHPUnit\Framework\TestCase;

class Issue71 extends TestCase
{
    public function test_Issue71currentCountInDB()
    {       
        $EventsqSet = new EventsqSet();
        $reply = $EventsqSet->countInDB();
        $this->assertSame(8,$reply,"Current number of events in the Q is not correct");
    }

    /**
     * @depends test_Issue71currentCountInDB
     */
    public function test_Issue71Renewnow()
    {
        global $_POST;
        $this->setupPost("Renewnow");
        $_POST["avatarUUID"] = "40000000-0000-0000-2800-000000000000";
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
        $this->assertSame(true,$rentalOld->loadByField("rentalUid",$split[0]),"Unable to load rental before to check changes");
        $this->assertSame(3,$rentalOld->getRenewals(),"Renewals value is not zero as expected");
        $Costandtime = new Costandtime();
        $Costandtime->process();
        $_POST["avatarUUID"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["avatarName"] = "Madpeter Zond";
        $_POST["amountpaid"] = 444;
        $Renewnow = new Renewnow();
        $this->assertSame("Not processed",$Renewnow->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Renewnow->getLoadOk(),"Load ok failed");
        $Renewnow->process();
        $this->assertStringStartsWith(
            "Payment accepted there is now:",
            $Renewnow->getOutputObject()->getSwapTagString("message"),
            "Incorrect message"
        );
        $this->assertSame(true,$Renewnow->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $rentalNew = new Rental();
        $this->assertSame(true,$rentalNew->loadByField("rentalUid",$split[0]),"Unable to load rental after to check changes");
        $this->assertSame(4,$rentalNew->getRenewals(),"Renewals value did not change");
    }

    /**
     * @depends test_Issue71Renewnow
     */
    public function test_Issue71reCountInDB()
    {       
        $EventsqSet = new EventsqSet();
        $reply = $EventsqSet->countInDB();
        $this->assertSame(9,$reply,"Current number of events in the Q is not correct");
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
        global $_POST, $slconfig;
        $_POST["method"] = "Renew";
        $_POST["action"] = $target;
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
    }
}

