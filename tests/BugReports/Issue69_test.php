<?php

namespace tests\BugReports;

use App\Endpoint\SecondLifeApi\ClientAutoSuspend\Next as ClientAutoSuspendNext;
use App\Endpoint\SecondLifeApi\Noticeserver\Next;
use App\R7\Model\Rental;
use App\R7\Set\ApirequestsSet;
use App\R7\Set\MessageSet;
use App\R7\Set\PackageSet;
use App\R7\Set\RentalSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
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

        $streams = new StreamSet();
        $streams->loadAll();
        $status = $streams->updateMultipleFieldsForCollection(["needWork","rentalLink"],[false,null]);
        $this->assertGreaterThanOrEqual(10,$status["changes"],"Incorrect number of streams updated: ".json_encode($status));
        $this->assertSame(true,$status["status"],"streams bulk update has failed");
        unset($streams);

        $apirequests = new ApirequestsSet();
        $apirequests->loadAll();
        $status = $apirequests->purgeCollection();
        $this->assertSame(2,$status["removed_entrys"],"Incorrect number of api requests removed: ".json_encode($status));
        $this->assertSame(true,$status["status"],"api requests purge has failed");
        unset($apirequests);

        $messageSet = new MessageSet();
        $messageSet->loadAll();
        $status = $messageSet->purgeCollection();
        $this->assertSame(12,$status["removed_entrys"],"Incorrect number of mail removed: ".json_encode($status));
        $this->assertSame(true,$status["status"],"mail purge has failed");
        unset($messageSet);

        $rentalSet = new RentalSet();
        $rentalSet->loadAll();
        $status = $rentalSet->updateMultipleFieldsForCollection(["message","avatarLink","noticeLink","expireUnixtime"],[null,1,5,time()-10]);
        $this->assertSame(4,$status["changes"],"Incorrect number of rentals updated: ".json_encode($status));
        $this->assertSame(true,$status["status"],"rentals bulk update has failed");
        unset($rentalSet);

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
        $this->assertSame("Not processed",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed"); 
        $this->assertSame(1,$rentalSet->countInDB($whereConfig),"There should have been one rental with the expired notice state");
        $apirequests = new ApirequestsSet();
        $this->assertSame(0,$apirequests->countInDB(),"There are API requests in the DB that should not be there");
    }


    /**
     * @depends test_expireRental
    */
    public function test_rentalApiPendingCorrect()
    {
        $rental = new Rental();
        $rental->loadByNoticeLink(6);
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");
        $this->assertSame(true,$rental->getApiPendingAutoSuspend(),"Rental does not have the pending auto suspend flag");
        $this->assertGreaterThan(time(),$rental->getApiPendingAutoSuspendAfter(),"Rental auto suspend is set for the past");
        $rental->setApiPendingAutoSuspendAfter(time()-10);
        $update = $rental->updateEntry();
        $this->assertSame("ok",$update["message"],"Incorrect update status message");
        $this->assertSame(true,$update["status"],"Incorrect update status");
    }

    /**
     * @depends test_rentalApiPendingCorrect
    */
    public function test_ClientAutoSuspend()
    {
        $Next = new ClientAutoSuspendNext();
        $this->assertSame("Not processed",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed"); 
        $apirequests = new ApirequestsSet();
        $this->assertSame(1,$apirequests->countInDB(),"Expected 1 api request in the Q");
        $rental = new Rental();
        $rental->loadByNoticeLink(6);
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");
        $this->assertSame(false,$rental->getApiPendingAutoSuspend(),"Rental should no longer have the pending suspend flag");
        $this->assertSame(true,$rental->getApiSuspended(),"Rental should have suspend flag");
        $this->assertSame(null,$rental->getApiPendingAutoSuspendAfter(),"Rental AutoSuspend unixtime should be null");
    }


}