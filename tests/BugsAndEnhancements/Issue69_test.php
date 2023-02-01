<?php

namespace tests\BugReports;

use App\Endpoint\Secondlifeapi\ClientAutoSuspend\Next as ClientAutoSuspendNext;
use App\Endpoint\Secondlifeapi\Noticeserver\Next;
use App\Models\Rental;
use App\Models\Sets\ApirequestsSet;
use App\Models\Sets\BotcommandqSet;
use App\Models\Sets\MessageSet;
use App\Models\Sets\PackageSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\ServerSet;
use App\Models\Sets\StreamSet;
use PHPUnit\Framework\TestCase;

class Issue69 extends TestCase
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
        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed"); 
        $this->assertSame(1,$rentalSet->countInDB($whereConfig)->items,"There should have been one rental with the expired notice state");
    }
}