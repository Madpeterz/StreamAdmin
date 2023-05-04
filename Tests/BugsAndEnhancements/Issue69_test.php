<?php

namespace tests\BugReports;

use App\Endpoint\Secondlifeapi\Noticeserver\Next;
use App\Models\Sets\BotcommandqSet;
use App\Models\Sets\MessageSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;
use Tests\Mytest;

class Issue69 extends Mytest
{
    public function test_reconfigWorkspace()
    {
        $rentalSet = new RentalSet();
        $rentalSet->loadAll();
        $status = $rentalSet->updateMultipleFieldsForCollection(["message","avatarLink","noticeLink","expireUnixtime"],[null,1,5,time()-10]);
        $this->assertSame(4,$status->changes,"Incorrect number of rentals updated: ".json_encode($status));
        $this->assertSame(true,$status->status,"rentals bulk update has failed");

        $streams = new StreamSet();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [$rentalSet->uniqueStreamLinks()],
            "matches" => ["NOT IN"],
            "types" => ["i"],
        ];
        $streams->loadWithConfig($whereConfig);
        $status = $streams->updateMultipleFieldsForCollection(["needWork","rentalLink"],[false,null]);
        $this->assertGreaterThanOrEqual(6,$status->changes,"Incorrect number of streams updated: ".json_encode($status));
        $this->assertSame(true,$status->status,"streams bulk update has failed");
        unset($streams);

        $messageSet = new MessageSet();
        $messageSet->loadAll();
        $status = $messageSet->purgeCollection();
        $this->assertSame(4,$status->itemsRemoved,"Incorrect number of mail removed: ".json_encode($status));
        $this->assertSame(true,$status->status,"mail purge has failed");
        unset($messageSet);

        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $status = $botmessageQ->purgeCollection();
        $this->assertSame(6,$status->itemsRemoved,"Incorrect number of bot commands removed: ".json_encode($status));
        $this->assertSame(true,$status->status,"bot comamnds purge has failed");
        unset($messageSet);
    }

    /**
     * @depends test_reconfigWorkspace
    */
    public function test_expireRental()
    {
        $rentalSet = new RentalSet();
        $whereConfig = [
            "fields" => ["noticeLink"],
            "values" => [6],
        ];
        $this->assertSame(0,$rentalSet->countInDB($whereConfig)->items,"There should have been zero rentals with the expired notice state");
        $this->setupPost();
        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed"); 
        $this->assertSame(1,$rentalSet->countInDB($whereConfig)->items,"There should have been one rental with the expired notice state");
    }

    protected function setupPost()
    {
        global $_POST, $system;
        $system->forceProcessURI("Noticeserver/Next");
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
        $raw = time()  ."NoticeserverNext". implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
    }
}