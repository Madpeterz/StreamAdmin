<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Renew\Costandtime;
use App\Endpoint\Secondlifeapi\Renew\Details;
use App\Endpoint\Secondlifeapi\Renew\Renewnow;
use App\Models\Rental;
use Tests\Mytest;

class SecondlifeApiRenew extends Mytest
{
    protected $package = null;
    public function test_Details()
    {
        global $_POST;
        $this->setupPost("Details");

        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $Details = new Details();
        $this->assertSame("ready",$Details->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Details->getLoadOk(),"Load ok failed");
        $Details->process();
        $this->assertSame("Client account: Test Buyer",$Details->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Details->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(1,$Details->getOutputObject()->getSwapTagInt("dataset_count"),"incorrect number of entrys reported");
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);
        $this->assertSame(3,count($split),"Dataset entry not formated as expected given: ".$dataset[0]);
    }

    /**
     * @depends test_Details
     */
    public function test_Costandtime()
    {
        sleep(2); // Delay a bit so we get the correct hours
        $this->setupPost("Costandtime");
        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $Details = new Details();
        $Details->process();
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);

        $this->setupPost("Costandtime");
        $_POST["rentalUid"] = $split[0];

        $Costandtime = new Costandtime();
        $this->assertSame("ready",$Costandtime->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Costandtime->getLoadOk(),"Load ok failed");
        $Costandtime->process();
        $this->assertSame(50,$Costandtime->getOutputObject()->getSwapTagInt("cost"),"incorrect package cost reported");
        $this->assertSame("20 days, 23 hours",$Costandtime->getOutputObject()->getSwapTagString("message"),"incorrect timeleft reported");
        $this->assertSame(true,$Costandtime->getOutputObject()->getSwapTagBool("status"),"marked as failed");
    }

    /**
     * @depends test_Costandtime
     */
    public function test_Renewnow()
    {
        $this->setupPost("Renewnow");
        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $Details = new Details();
        $Details->process();
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);
        $this->setupPost("Costandtime");
        $_POST["rentalUid"] = $split[0];
        $rentalOld = new Rental();
        $this->assertSame(true,$rentalOld->loadByRentalUid($split[0])->status,"Unable to load rental before to check changes");
        $this->assertSame(0,$rentalOld->getRenewals(),"Renewals value is not zero as expected");
        $Costandtime = new Costandtime();
        $Costandtime->process();
        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["amountpaid"] = 50;
        $Renewnow = new RenewNow();
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
        $this->assertSame(1,$rentalNew->getRenewals(),"Renewals value did not change");
    }

    protected function setupPost(string $target)
    {
        global $_POST, $system;
        $system->forceProcessURI("Renew/".$target);
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
        $raw = time()  . "Renew".$target.implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
    }
}
