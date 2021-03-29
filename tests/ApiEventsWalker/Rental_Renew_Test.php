<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Apirequests\Next;
use App\Endpoint\SecondLifeApi\Renew\Renewnow;
use App\R7\Model\Rental;
use App\R7\Model\Stream;
use App\R7\Set\ApirequestsSet;
use PHPUnit\Framework\TestCase;

class RentalRenew_Test extends TestCase
{
    public function test_UI_Renew()
    {
        $stream = new Stream();
        $this->assertSame(true,$stream->loadByField("port",9998),"Failed to load stream");
        $rental = new Rental();
        $this->assertSame(true,$rental->loadByField("streamLink",$stream->getId()),"Failed to load rental");
        $this->setupPost("Renewnow");
        $_POST["rentalUid"] = $rental->getRentalUid();
        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["amountpaid"] = 50;
        $Renewnow = new Renewnow();
        $this->assertSame("Not processed",$Renewnow->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Renewnow->getLoadOk(),"Load ok failed");
        $Renewnow->process();
        $this->assertStringStartsWith(
            "Payment accepted there is now",
            $Renewnow->getOutputObject()->getSwapTagString("message"),
            "Incorrect message"
        );
        $this->assertSame(true,$Renewnow->getOutputObject()->getSwapTagBool("status"),"marked as failed");
    }

    /**
     * @depends test_UI_Renew
    */
    public function test_FirstActionInQ()
    {
        $apiRequests = new ApirequestsSet();
        $this->assertSame(true,$apiRequests->loadAll()["status"],"Status check failed");
        $this->assertSame(1,$apiRequests->getCount(),"Incorrect number of requests in the Q");
    }

    /**
     * @depends test_FirstActionInQ
    */
    public function test_ActionLoops()
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Apirequests";
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
        
        $exit = false;
        $loops=0;
        $expected_replys = [
            "ok",
            "passed",
            "none"
        ];
        while($exit == false)
        {
            $apiRequests = new ApirequestsSet();
            $status = $apiRequests->loadAll()["status"];
            $this->assertSame(true,$status,"Status check failed");
            if($status == false)
            {
                $exit = true;
                break;
            }
            if($apiRequests->getCount() == 0) {
                $exit = true;
                break;
            }
            $Next = new Next();
            $this->assertSame("Not processed",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
            $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
            $Next->process();
            $this->assertSame(true,in_array($Next->getOutputObject()->getSwapTagString("message"),$expected_replys),"incorrect reply on loop: ".$loops."");
            $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
            if($Next->getOutputObject()->getSwapTagBool("status") == false)
            {
                $exit = true;
                break;
            }
            $loops++;
        }
        $this->assertSame(1,$loops,"Incorrect number of API steps");
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

