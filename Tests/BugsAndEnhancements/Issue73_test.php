<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Botcommandq\Next;
use App\Endpoint\Secondlifeapi\Buy\Startrental;
use App\Models\Botconfig;
use App\Models\Package;
use App\Models\Sets\BotcommandqSet;
use Tests\Mytest;

class Issue73 extends Mytest
{
    protected ?package $package = null;
    public function test_ResetDb()
    {
        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(3,$botmessageQ->getCount(),"Incorrect number of bot commands loaded");
        $status = $botmessageQ->purgeCollection();
        $this->assertSame(3,$status->itemsRemoved,"Incorrect number of bot commands removed: ".json_encode($status));
        $this->assertSame(true,$status->status,"bot comamnds purge has failed");
        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(0,$reply->items,"Current number of events in the Q is not correct: ".$botcommandSet->getLastSql()); 
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
        $this->assertSame(true,$reply->status,"Failed to update bot config");
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
        $this->assertSame("ready",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Details should be with you shortly",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(0,$startRental->getOutputObject()->getSwapTagInt("owner_payment"),"incorrect owner payment");

        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(1,$reply->items,"Current number of events in the Q is not correct: ".$botcommandSet->getLastSql()); 
        // Should have a group invite in the Q
    }

    /**
     * @depends test_buyStream
     */
    public function test_SlService()
    {       
        global $_POST, $system;

        $system->forceProcessURI("Botcommandq/Next");
        $_POST["mode"] = "botcommandqserver";
        $_POST["objectuuid"] = "b36971ef-f2a5-f461-425c-81bbc473deb8";
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
        $raw = time()  ."BotcommandqNext". implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("send",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertStringStartsWith("GroupInvite",$Next->getOutputObject()->getSwapTagString("cmd"),"Expected group invite command");

        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(0,$reply->items,"Current number of events in the Q is not correct"); 
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
        $this->assertSame(true,$reply->status,"Failed to update bot HTTP settings");
    }

    /**
     * @depends test_setupHttpMode
     */
    public function test_cronJobBotHttp()
    {
        global $_SERVER;
        $_SERVER["argv"] = [];
        $_SERVER["argv"]["t"] = "Botcommandq";
        $_SERVER["argv"]["b"] = "true";
        require "src/App/CronTab.php";
        $this->assertStringContainsString('"message":"nowork","ticks":1',$this->getActualOutputForAssertion(),"Reply from crontab is not as we expect");
    }



    

    protected function setupPostBuy(string $target)
    {
        global $system;
        $system->forceProcessURI("Buy/".$target);
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
        $raw = time()  ."Buy".$target. implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $this->package = new Package();
        $this->package->loadID(1);
        $this->assertSame("UnitTestPackage",$this->package->getName(),"Test package not loaded");
    }
}

