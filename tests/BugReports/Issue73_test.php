<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Buy\Startrental;
use App\R7\Model\Botconfig;
use App\R7\Model\Package;
use App\R7\Model\Slconfig;
use App\R7\Set\BotcommandqSet;
use PHPUnit\Framework\TestCase;

class Issue73 extends TestCase
{
    public function test_currentCountInDB()
    {       
        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(0,$reply,"Current number of events in the Q is not correct");
    }

    /**
     * @depends test_currentCountInDB
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
        

        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["amountpaid"] = $this->package->getCost() + 4;

        $startRental = new Startrental();
        $this->assertSame("Not processed",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Payment amount not accepted",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(false,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as ok");

        $botcommandSet = new BotcommandqSet();
        $reply = $botcommandSet->countInDB();
        $this->assertSame(1,$reply,"Current number of events in the Q is not correct"); 
        // Should have a group invite in the Q
    }

    /**
     * @depends test_buyStream
     */
    public function test_RecheckCurrentCountInDB()
    {       

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

