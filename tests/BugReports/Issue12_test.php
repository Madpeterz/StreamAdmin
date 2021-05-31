<?php

namespace tests\BugReports;

use App\Endpoint\Control\Package\Create;
use App\Endpoint\SecondLifeApi\Buy\Startrental;
use App\R7\Model\Package;
use App\R7\Model\Server;
use App\R7\Set\ApirequestsSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
use PHPUnit\Framework\TestCase;

/*
https://github.com/Madpeterz/StreamAdmin/issues/12
Setup server with azuracast
Sell a stream
See error "
{"message":"
    API server logic has failed on ApiLogicBuy: 
    Api Azurecast does not support: getEventStartSyncUsername
            ",
    "status":"0",
    "method":"Buy",
    "action":"Startrental",
    "owner_payment":"0",
    "render":"secondlifeAjax"
}"
*/
class Issue12 extends TestCase
{
    public function test_ClearPendingAPI()
    {
        $pending = new ApirequestsSet();
        $pending->loadAll();
        if($pending->getCount() > 0)
        {
            $this->assertSame(true,$pending->purgeCollection()["status"],"Unable to clear API");
        }
        $this->assertSame(true,true,"all done");
    }

    /**
     * @depends test_ClearPendingAPI
    */
    public function test_SetupServer()
    {
        $servers = new ServerSet();
        $servers->loadNewest(1);
        $server = $servers->getFirst();
        $server->setApiServerStatus(1);
        $server->setApiSyncAccounts(1);
        $server->setOptToggleStatus(1);
        $server->setOptPasswordReset(1);
        $server->setOptAutodjNext(1);
        $server->setOptToggleAutodj(1);
        $server->setEventEnableStart(1);
        $server->setEventEnableRenew(1);
        $server->setEventDisableExpire(1);
        $server->setEventDisableRevoke(1);
        $server->setEventResetPasswordRevoke(1);
        $server->setEventClearDjs(1);
        $server->setApiLink(6);
        $server->setApiPassword("issue12Test");
        $this->assertSame(true,$server->updateEntry()["status"],"Unable to update server");
    }

    
    /**
     * @depends test_SetupServer
    */
    public function test_SetupPackage()
    {
        global $_POST;
        $PackageCreateHandler = new Create();
        $_POST["name"] = "Issue12package";
        $_POST["templateLink"] = 1;
        $_POST["cost"] = 66;
        $_POST["days"] = 5;
        $_POST["bitrate"] = 56;
        $_POST["listeners"] = 10;
        $_POST["textureSoldout"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSmall"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSelected"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["autodj"] = false;
        $_POST["autodjSize"] = 0;
        $_POST["apiTemplate"] = "None";
        $_POST["servertypeLink"] = 1;
        $_POST["welcomeNotecardLink"] = 1;
        $_POST["setupNotecardLink"] = 1;
        $PackageCreateHandler->process();
        $statuscheck = $PackageCreateHandler->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertStringContainsString("Package created",$statuscheck->getSwapTagString("message"));
    }

    /**
     * @depends test_SetupPackage
    */
    public function test_SetupStream()
    {
        $server = new Server();
        $server->loadByApiPassword("issue12Test");
        $package = new Package();
        $package->loadByName("Issue12package");
        $streams = new StreamSet();
        $streams->loadNewest(1);
        $stream = $streams->getFirst();
        $stream->setNeedWork(0);
        $stream->setPackageLink($package->getId());
        $stream->setServerLink($server->getId());
        $stream->setAdminPassword("issue12testing");
        $stream->setApiConfigValue1(12);
        $stream->setApiConfigValue2(33);
        $stream->setApiConfigValue3(12);
        $this->assertSame(true,$stream->updateEntry()["status"],"Unable to update stream");
    }

    /**
     * @depends test_SetupStream
    */
    public function test_SellStream()
    {
        $this->setupPost("Startrental");

        $package = new Package();
        $package->loadByName("Issue12package");
        $_POST["avatarUUID"] = "F39c3e36-F9b3-30e5-F229-0Ffa3db30736";
        $_POST["avatarName"] = "Issue12 Buyer";
        $_POST["packageuid"] = $package->getPackageUid();
        $_POST["amountpaid"] = $package->getCost() * 1;

        $startRental = new Startrental();
        $this->assertSame("Not processed",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Details should be with you shortly",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(0,$startRental->getOutputObject()->getSwapTagInt("owner_payment"),"incorrect owner payment");
    }


    /**
     * @depends test_SellStream
    */
    public function test_CheckAPIcount()
    {
        $pending = new ApirequestsSet();
        $pending->loadAll();
        $this->assertSame(1,$pending->getCount(),"Incorrect number of pending API");
    }



    protected function setupPost(string $target)
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Buy";
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
        $this->package = new Package();
        $this->package->loadID(1);
        $this->assertSame("UnitTestPackage",$this->package->getName(),"Test package not loaded");
    }
}