<?php

namespace tests\BugReports;

use App\Endpoint\SecondLifeApi\ClientAutoSuspend\Next as ClientAutoSuspendNext;
use App\Endpoint\SecondLifeApi\Noticeserver\Next;
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
        $packages = new PackageSet();
        $loadStatus = $packages->loadAll();
        $this->assertSame("ok",$loadStatus["message"],"Packages did not load correctly");
        $this->assertSame(true,$loadStatus["status"],"Packages did not load correctly");
        $status = $packages->updateFieldInCollection("apiAutoSuspendDelayHours",2);
        $this->assertSame(5,$status["changes"],"Incorrect number of packages updated: ".json_encode($status));
        $this->assertSame(true,$status["status"],"Packages bulk update has failed");
        unset($packages);

        $rentalSet = new RentalSet();
        $rentalSet->loadAll();
        $status = $rentalSet->updateMultipleFieldsForCollection(["message","avatarLink","noticeLink","expireUnixtime"],[null,1,5,time()-10]);
        $this->assertSame(4,$status["changes"],"Incorrect number of rentals updated: ".json_encode($status));
        $this->assertSame(true,$status["status"],"rentals bulk update has failed");

        $streams = new StreamSet();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [$rentalSet->uniqueStreamLinks()],
            "matches" => ["NOT IN"],
            "types" => ["i"],
        ];
        $streams->loadWithConfig($whereConfig);
        $status = $streams->updateMultipleFieldsForCollection(["needWork","rentalLink"],[false,null]);
        $this->assertGreaterThanOrEqual(6,$status["changes"],"Incorrect number of streams updated: ".json_encode($status));
        $this->assertSame(true,$status["status"],"streams bulk update has failed");
        unset($streams);

        $messageSet = new MessageSet();
        $messageSet->loadAll();
        $status = $messageSet->purgeCollection();
        $this->assertSame(6,$status["removed_entrys"],"Incorrect number of mail removed: ".json_encode($status));
        $this->assertSame(true,$status["status"],"mail purge has failed");
        unset($messageSet);

        $botmessageQ = new BotcommandqSet();
        $botmessageQ->loadAll();
        $status = $botmessageQ->purgeCollection();
        $this->assertSame(7,$status["removed_entrys"],"Incorrect number of bot commands removed: ".json_encode($status));
        $this->assertSame(true,$status["status"],"bot comamnds purge has failed");
        unset($messageSet);

        $bulkUpdate = [
            "apiLink" => 2,
            "apiURL" => "http://127.0.0.1/fake/centova.php",
            "apiUsername" => "admin",
            "apiPassword" => "fake",
            "apiServerStatus" => true,
            "apiSyncAccounts" => true,
            "optPasswordReset" => true,
            "optAutodjNext" => true,
            "optToggleAutodj" => true,
            "optToggleStatus" => true,
            "eventEnableStart" => true,
            "eventStartSyncUsername" => true,
            "eventEnableRenew" => true,
            "eventDisableExpire" => true,
            "eventDisableRevoke" => true,
            "eventRevokeResetUsername" => true,
            "eventResetPasswordRevoke" => true,
            "eventClearDjs" => true,
            "eventRecreateRevoke" => true,
            "lastApiSync" => time(),
            "eventCreateStream" => true,
            "eventUpdateStream" => true,
        ];
        $serverSet = new ServerSet();
        $serverSet->loadAll();
        $status = $serverSet->updateMultipleFieldsForCollection(array_keys($bulkUpdate),array_values($bulkUpdate));
        $this->assertSame(3,$status["changes"],"Incorrect number of servers updated: ".json_encode($status));
        $this->assertSame(true,$status["status"],"servers bulk update has failed");
        unset($serverSet);
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
        $this->assertSame(0,$rentalSet->countInDB($whereConfig),"There should have been zero rentals with the expired notice state");
        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed"); 
        $this->assertSame(1,$rentalSet->countInDB($whereConfig),"There should have been one rental with the expired notice state");
    }
}