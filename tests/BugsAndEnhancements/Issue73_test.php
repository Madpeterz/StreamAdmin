<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\BotCommandQ\Next;
use App\Endpoint\SecondLifeApi\Buy\Startrental;
use App\Models\Botconfig;
use App\Models\Package;
use App\Models\Sets\BotcommandqSet;
use PHPUnit\Framework\TestCase;

class Issue73 extends TestCase
{
    protected ?package $package = null;
    public function test_ResetDb()
    {      
        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $status = $botmessageQ->purgeCollection();
        $this->assertSame(1,$status["removed_entrys"],"Incorrect number of bot commands removed: ".json_encode($status));
        $this->assertSame(true,$status["status"],"bot comamnds purge has failed");
        unset($messageSet);
    }

    /**
     * @depends test_ResetDb
     */
    public function test_EnableGroupInvite()
    {
        $Botconfig = new Botconfig();
        $Botconfig->loadID(1);
        $this->assertSame(true,$Botconfig->isLoaded(),"Failed to load bot config");
        $Botconfig->setInvites(true);
        $Botconfig->setInviteGroupUUID("00000000-0001-0000-0000-000000000000");
        $reply = $Botconfig->updateEntry();
        $this->assertSame(true,$reply["status"],"Failed to update bot config");
    }

    /**
     * @depends test_EnableGroupInvite
     */
    public function test_buyStream()
    {
        global $_POST;
        $this->setupPostBuy("Startrental");

        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["amountpaid"] = $this->package->getCost() * 3;

        $startRental = new Startrental();
        $this->assertSame("Not processed",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Details should be with you shortly",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(0,$startRental->getOutputObject()->getSwapTagInt("owner_payment"),"incorrect owner payment");

        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(1,$reply,"Current number of events in the Q is not correct"); 
        // Should have a group invite in the Q
    }

    /**
     * @depends test_buyStream
     */
    public function test_SlService()
    {       
        global $_POST, $slconfig;
        $_POST["method"] = "Botcommandq";
        $_POST["action"] = "Next";
        $_POST["mode"] = "botcommandqserver";
        $_POST["objectuuid"] = "b36971ef-f2a5-f461-425c-81bbc473deb8";
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
        $Next = new Next();
        $this->assertSame("Not processed",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("send",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertStringStartsWith("GroupInvite",$Next->getOutputObject()->getSwapTagString("cmd"),"Expected group invite command");

        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(0,$reply,"Current number of events in the Q is not correct"); 
    }

    /**
     * @depends test_SlService
     */
    public function test_setupHttpMode()
    {
        $botconfig = new Botconfig();
        $botconfig->loadID(1);
        $this->assertSame(true,$botconfig->isLoaded(),"Failed to load bot config");
        $botconfig->setHttpMode(true);
        $botconfig->setHttpURL("http://127.0.0.1/fake/secondbot.php/");
        $botconfig->setHttpToken("lolwhatlol");
        $reply = $botconfig->updateEntry();
        $this->assertSame(true,$reply["status"],"Failed to update bot HTTP settings");
    }

    /**
     * @depends test_setupHttpMode
     */
    public function test_cronJobBotHttp()
    {
        if(defined("TESTING") == false) { 
            define("TESTING",true);
        }
        global $_SERVER;
        $_SERVER["argv"]["t"] = "BotcommandQ";
        $_SERVER["argv"]["b"] = "true";
        include "src/App/CronJob/CronTab.php";
        $this->assertStringContainsString('"ticks":1,"sleep":0',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }



    

    protected function setupPostBuy(string $target)
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

