<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Renew\Details;
use App\Endpoint\SecondLifeHudApi\Rentals\Costs;
use App\Endpoint\SecondLifeHudApi\Rentals\GetApiStatus;
use App\Endpoint\SecondLifeHudApi\Rentals\GetDetails;
use App\Endpoint\SecondLifeHudApi\Rentals\GetServerList;
use App\Endpoint\SecondLifeHudApi\Rentals\GetServerStatus;
use App\Endpoint\SecondLifeHudApi\Rentals\GetServerURL;
use App\Endpoint\SecondLifeHudApi\Rentals\Topup;
use App\R7\Model\Rental;
use PHPUnit\Framework\TestCase;

class SecondlifeHudRentals extends TestCase
{
    public function test_ForceSetHudLinkCode()
    {
        global $slconfig;
        $slconfig->setHudLinkCode("asb123dvb432");
        $this->assertSame(true,$slconfig->updateEntry()["status"],"Code update failed");
    }
    /**
     * @depends test_ForceSetHudLinkCode
     */
    public function test_Costs()
    {
        $this->SetupPost("Costs");
        $this->setupRentalUid();

        $Costs = new Costs();
        $this->assertSame("Not processed",$Costs->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Costs->getLoadOk(),"Load ok failed");
        $Costs->process();
        $this->assertSame("ok",$Costs->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Costs->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertGreaterThan(time(),$Costs->getOutputObject()->getSwapTagInt("old_expire_time"),"marked as expired");
        $this->assertGreaterThan(0,$Costs->getOutputObject()->getSwapTagInt("cost"),"cost value not set");
        $this->assertSame("289c3e36-69b3-40c5-9229-0c6a5d230766",$Costs->getOutputObject()->getSwapTagString("systemowner"),"incorrect owner uuid returned");
    }

    /**
     * @depends test_ForceSetHudLinkCode
     */
    public function test_GetApiStatus()
    {
        $this->SetupPost("GetApiStatus");
        $this->setupRentalUid();

        $GetApiStatus = new GetApiStatus();
        $this->assertSame("Not processed",$GetApiStatus->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$GetApiStatus->getLoadOk(),"Load ok failed");
        $GetApiStatus->process();
        $this->assertSame("seeflags",$GetApiStatus->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$GetApiStatus->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(0,$GetApiStatus->getOutputObject()->getSwapTagInt("autodjnext"),"flag: autodjnext not set");
    }

    /**
     * @depends test_ForceSetHudLinkCode
     */
    public function test_GetDetails()
    {
        $this->SetupPost("GetDetails");
        $this->setupRentalUid();

        $GetDetails = new GetDetails();
        $this->assertSame("Not processed",$GetDetails->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$GetDetails->getLoadOk(),"Load ok failed");
        $GetDetails->process();
        $this->assertSame(
            "Details request accepted, it should be with you shortly!",
            $GetDetails->getOutputObject()->getSwapTagString("message"),
            "incorrect reply"
        );
        $this->assertSame(true,$GetDetails->getOutputObject()->getSwapTagBool("status"),"marked as failed");
    }

    /**
     * @depends test_ForceSetHudLinkCode
     */
    public function test_GetServerStatus()
    {
        $this->SetupPost("GetServerStatus");
        $this->setupRentalUid();

        $GetServerStatus = new GetServerStatus();
        $this->assertSame("Not processed",$GetServerStatus->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$GetServerStatus->getLoadOk(),"Load ok failed");
        $GetServerStatus->process();
        $this->assertSame("ok",$GetServerStatus->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$GetServerStatus->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertNotNull($GetServerStatus->getOutputObject()->getSwapTagString("timeleft"),"timeleft is null");
    }

    /**
     * @depends test_ForceSetHudLinkCode
     */
    public function test_GetServerURL()
    {
        $this->SetupPost("GetServerURL");
        $this->setupRentalUid();

        $GetServerURL = new GetServerURL();
        $this->assertSame("Not processed",$GetServerURL->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$GetServerURL->getLoadOk(),"Load ok failed");
        $GetServerURL->process();
        $this->assertSame("ok",$GetServerURL->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$GetServerURL->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertNotNull($GetServerURL->getOutputObject()->getSwapTagString("serverurl"),"serverurl is null");
        $this->assertNotNull($GetServerURL->getOutputObject()->getSwapTagString("servertype"),"servertype is null");
    }

    /**
     * @depends test_ForceSetHudLinkCode
     */
    public function test_GetServerList()
    {
        $this->SetupPost("GetServerList");
        $this->setupRentalUid();

        $GetServerList = new GetServerList();
        $this->assertSame("Not processed",$GetServerList->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$GetServerList->getLoadOk(),"Load ok failed");
        $GetServerList->process();
        $this->assertSame("ok",$GetServerList->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$GetServerList->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(1,$GetServerList->getOutputObject()->getSwapTagInt("count"),"incorrect number of entrys");
        $this->assertSame(1,count($GetServerList->getOutputObject()->getSwapTagArray("uids")),"incorrect number of uids returned");
    }

    /**
     * @depends test_ForceSetHudLinkCode
     */
    public function test_Topup()
    {
        global $slconfig;
        $this->SetupPost("Topup");
        $this->setupRentalUid();
        $rental = new Rental();
        $this->assertSame(true,$rental->loadByField("rentalUid",$_POST["rentalUid"]),"Unable to load rental");

        global $_POST;
        $_POST["amount"] = 50;
        $_POST["transactionid"] = "ffbb3311-ffbb-ffbb-ffbb-ffbb33114466";
        $_POST["tidtime"] = time();
        $bits = [
            $_POST["rentalUid"],
            $_POST["amount"],
            $_POST["transactionid"],
            $_POST["tidtime"],
            "289c3e36-69b3-40c5-9229-0c6a5d230766",
            $slconfig->getHudLinkCode(),
            $rental->getExpireUnixtime(),
        ];
        $raw = implode("", $bits);
        $_POST["tidhash"] = sha1($raw);

        $Topup = new Topup();
        $this->assertSame("Not processed",$Topup->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Topup->getLoadOk(),"Load ok failed");
        $Topup->process();
        $this->assertStringContainsString("Payment accepted",$Topup->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Topup->getOutputObject()->getSwapTagBool("status"),"marked as failed");
    }


    protected function setupRentalUid()
    {
        global $_POST;
        $_POST["avatarUUID"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $Details = new Details();
        $Details->process();
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);
        $_POST["rentalUid"] = $split[0];
    }


    protected function SetupPost($action)
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Rentals";
        $_POST["action"] = $action;
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
        $raw = time()  . implode("",$real) . $slconfig->getHudLinkCode();
        $_POST["hash"] = sha1($raw);
    }

}
