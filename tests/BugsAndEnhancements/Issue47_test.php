<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Buy\Startrental;
use App\R7\Model\Botconfig;
use App\R7\Model\Package;
use App\R7\Set\BotcommandqSet;
use App\R7\Set\MessageSet;
use App\R7\Set\PackageSet;
use PHPUnit\Framework\TestCase;

class Issue47 extends TestCase
{
    public function test_addGroupInviteToMessageQ()
    {       
        global $sql;
        $packageSet = new PackageSet();
        $reply = $packageSet->loadAll();
        $this->assertSame("ok",$reply["message"],"Unable to load all packages");
        $this->assertSame(true,$reply["status"],"Unable to load all packages");
        $reply = $packageSet->updateFieldInCollection("enableGroupInvite",1);
        $this->assertSame("ok",$reply["message"],"Unable to bulk update enable group invite");
        $this->assertSame(true,$reply["status"],"Unable to bulk update enable group invite");
        $botconfig = new Botconfig();
        $reply = $botconfig->loadID(1);
        $this->assertSame(true,$reply,"Failed to load botconfig");
        $botconfig->setInvites(1);
        $botconfig->setInviteGroupUUID("test");
        $reply = $botconfig->updateEntry();
        $this->assertSame("ok",$reply["message"],"Unable to update bot config");
        $this->assertSame(true,$reply["status"],"Unable to update bot config");
        $sql->sqlSave();

        $this->setupPost("Startrental");

        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["amountpaid"] = $this->package->getCost() * 3;

        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(6,$botmessageQ->getCount(),"Incorrect number of messages in bot command Q");

        $startRental = new Startrental();
        $this->assertSame("Not processed",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Details should be with you shortly",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(0,$startRental->getOutputObject()->getSwapTagInt("owner_payment"),"incorrect owner payment");
        
        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $this->assertSame(7,$botmessageQ->getCount(),"Incorrect number of messages in bot command Q");

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
