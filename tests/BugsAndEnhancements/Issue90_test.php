<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Client\Revoke;
use App\Endpoint\Control\Client\Update;
use App\Endpoint\SecondLifeApi\Buy\StartRental;
use App\Endpoint\SecondLifeApi\Noticeserver\Next;
use App\Endpoint\SecondLifeApi\Renew\Renewnow;
use App\Helpers\NoticesHelper;
use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Sets\DetailSet;
use App\Models\Sets\NotecardSet;
use App\Models\Sets\RentalSet;
use PHPUnit\Framework\TestCase;

class Issue90 extends TestCase
{
    protected Package $package;
    public function test_NoticeHelper()
    {
        $noticesHelper = new NoticesHelper();

        $tests = [
            73 => 2,
            (31*24) => 10,
            6 => 4,
            2 => 5,
            (6*24) => 1,
            0 => 6,
            -2 => 6,
            5 => 5,
            24 => 4,
            72 => 3,
            120 => 2,
            168 => 1,
            350 => 10
        ];

        foreach($tests as $hours => $expectedID)
        {
            $selectedID = $noticesHelper->getNoticeLevel($hours);
            $this->assertSame($expectedID,$selectedID,"Incorrect notice ID returned");
        }
    }

    /**
     * @depends test_NoticeHelper
     */
    public function test_StartRental()
    {
        global $_POST;
        $this->setupPostBuy("StartRental");

        $_POST["avatarUUID"] = "90909090-9090-9090-9090-909090900091";
        $_POST["avatarName"] = "Issue90 Buyer1";
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["amountpaid"] = $this->package->getCost() * 4;

        $startRental = new StartRental();
        $this->assertSame("ready",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Details should be with you shortly",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(0,$startRental->getOutputObject()->getSwapTagInt("owner_payment"),"incorrect owner payment");

        $avatar = new Avatar();
        $avatar->loadByAvatarUUID($_POST["avatarUUID"]);
        $this->assertSame(true,$avatar->isLoaded(),"Failed to load avatar");

        $rental = new Rental();
        $rental->loadByAvatarLink($avatar->getId());
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");

        $this->assertSame(10,$rental->getNoticeLink(),"Rental assigned incorrect notice level");
    }

    /**
     * @depends test_StartRental
     */
    public function test_EndAllOtherRentals()
    {
        global $_POST;
        $_POST["avatarUUID"] = "90909090-9090-9090-9090-909090900091";
        $avatar = new Avatar();
        $avatar->loadByAvatarUUID($_POST["avatarUUID"]);
        $this->assertSame(true,$avatar->isLoaded(),"Failed to load avatar");

        $rental = new Rental();
        $rental->loadByAvatarLink($avatar->getId());
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");

        $detailsSet = new DetailSet();
        $detailsSet->loadAll();
        $this->assertSame(2, $detailsSet->getCount(), "incorrect number of pending details requests");
        $reply = $detailsSet->purgeCollection();
        $this->assertSame(true, $reply->status, "Detail requests not removed");

        $notecardSet = new NotecardSet();
        $notecardSet->loadAll();
        $reply = $notecardSet->purgeCollection();
        $this->assertSame(1, $reply->itemsRemoved, "Incorrect number of pending notecards removed");

        $rentalSet = new RentalSet();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [$rental->getId()],
            "matches" => ["!="],
        ];
        $rentalSet->loadWithConfig($whereConfig);
        $this->assertSame(5,$rentalSet->getCount(),"Incorrect number of rentals loaded");
        global $system, $_POST;
        $_POST["accept"] = "Accept";
        foreach($rentalSet as $rentalRm) {
            $system->setPage($rentalRm->getRentalUid());
            $removeRental = new Revoke();
            $removeRental->process();
            $statuscheck = $removeRental->getOutputObject();
            $this->assertStringContainsString("Client rental revoked",$statuscheck->getSwapTagString("message"));
            $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");            
        }
    }

    /**
     * @depends test_EndAllOtherRentals
     */
    public function test_ExpireRentalInSteps()
    {
        global $system, $_POST;
        $_POST["avatarUUID"] = "90909090-9090-9090-9090-909090900091";
        $avatar = new Avatar();
        $avatar->loadByAvatarUUID($_POST["avatarUUID"]);
        $this->assertSame(true,$avatar->isLoaded(),"Failed to load avatar");
        $rental = new Rental();
        $rental->loadByAvatarLink($avatar->getId());
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");

        // set rental to 6days 23hours
        $setTimes = [
            ($system->unixtimeDay()*6) + ($system->unixtimeHour()*23) => 1,
            ($system->unixtimeDay()*4) + ($system->unixtimeHour()*23) => 2,
            ($system->unixtimeDay()*2) + ($system->unixtimeHour()*23) => 3,
            ($system->unixtimeHour()*23) => 4,
            ($system->unixtimeHour()*4) => 5,
            -5 => 6,
        ];

        foreach($setTimes as $addUnixtime => $expectedNoticeLevel)
        {
            $rental->setExpireUnixtime(time() + $addUnixtime);
            $update = $rental->updateEntry();
            $this->assertSame(true,$update->status,"Rental expires not updated");
            // Tick notice server
            $Next = new Next();
            $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
            $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
            $Next->process();
            $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
            $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
            // Recheck rental
            $rental = new Rental();
            $rental->loadByAvatarLink($avatar->getId());
            $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");
            $this->assertSame($expectedNoticeLevel,$rental->getNoticeLink(),"Incorrect notice level assigned");
        }
    }

    /**
     * @depends test_ExpireRentalInSteps
     */
    public function test_RenewBackToActiveInSteps()
    {
        global $_POST, $system;
        $_POST["avatarUUID"] = "90909090-9090-9090-9090-909090900091";
        $avatar = new Avatar();
        $avatar->loadByAvatarUUID($_POST["avatarUUID"]);
        $this->assertSame(true,$avatar->isLoaded(),"Failed to load avatar");
        $rental = new Rental();
        $rental->loadByAvatarLink($avatar->getId());
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");
        $this->package = new Package();
        $this->package->loadID(1);
        $this->assertSame(true,$this->package->isLoaded(),"Failed to load package");
        $this->package->setCost(5);
        $this->package->setDays(1);
        $reply = $this->package->updateEntry();
        $this->assertSame(true,$reply->status,"Failed to update package");
        $paymentAmounts = [
            ["amount"=>5,"start"=>6,"end"=>4],
            ["amount"=>10,"start"=>4,"end"=>3],
            ["amount"=>10,"start"=>3,"end"=>2],
            ["amount"=>10,"start"=>2,"end"=>1],
            ["amount"=>10,"start"=>1,"end"=>10],
        ];
        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["rentalUid"] = $rental->getRentalUid();
        $this->setupRenewPost("Renewnow");
        $log = "";
        foreach($paymentAmounts as $entry) {
            $amount = $entry["amount"];
            $expectedNoticeLevel = [$entry["start"],$entry["end"]];
            $log .= "Checking from ".$expectedNoticeLevel[0]." to ".$expectedNoticeLevel[1];
            $_POST["amountpaid"] = $amount;
            $this->assertSame($expectedNoticeLevel[0],$rental->getNoticeLink(),
            "[".$amount."@".$expectedNoticeLevel[1]."] Incorrect prerenewal notice level ".$log);
            $Renewnow = new Renewnow();
            $oldExpireTime = $rental->getExpireUnixtime();
            $this->assertSame("ready",$Renewnow->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
            $this->assertSame(true,$Renewnow->getLoadOk(),"Load ok failed");
            $Renewnow->process();
            $this->assertStringStartsWith(
                "Payment accepted there is now:",
                $Renewnow->getOutputObject()->getSwapTagString("message"),
                "[".$amount."@".$expectedNoticeLevel[1]."] Incorrect message ".$log
            );
            $this->assertSame(true,$Renewnow->getOutputObject()->getSwapTagBool("status"),"[".$amount."@".$expectedNoticeLevel[1]."] marked as failed ".$log);
            // Recheck rental
            $rental = new Rental();
            $rental->loadByAvatarLink($avatar->getId());
            $this->assertSame(true,$rental->isLoaded(),"[".$amount."@".$expectedNoticeLevel[1]."] Failed to load rental ".$log);
            $expectedRenewalTime = $oldExpireTime + ($system->unixtimeDay() * ($amount/5));
            $this->assertSame($expectedRenewalTime,$rental->getExpireUnixtime(),
            "[".$amount."@".$expectedNoticeLevel[1]."] rental expire unixtime is not as expected ".$log);
            $this->assertSame($expectedNoticeLevel[1],$rental->getNoticeLink(),
            "[".$amount."@".$expectedNoticeLevel[1]."] Incorrect notice level assigned ".$log);
            $log .= " - ok\n";
        }
    }

    /**
     * @depends test_RenewBackToActiveInSteps
     */
    public function test_RemoveTimeViaWebUi()
    {
        global $_POST, $system;
        $_POST = [];
        $_POST["avatarUUID"] = "90909090-9090-9090-9090-909090900091";
        $avatar = new Avatar();
        $reply = $avatar->loadByAvatarUUID($_POST["avatarUUID"]);
        $this->assertSame(true, $reply->status, "Failed to load avatar");
        $this->assertSame(true,$avatar->isLoaded(),"Failed to load avatar");
        $rental = new Rental();
        $rental->loadByAvatarLink($avatar->getId());
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");
        $this->assertSame(11, $rental->getId(), "Incorrect rental ID found!");

        $system->setPage($rental->getRentalUid());
        
        $manageProcess = new Update();
        $_POST["message"] = $rental->getMessage();
        $_POST["adjustment_dir"] = "false";
        $_POST["adjustment_hours"] = "0";
        $_POST["adjustment_days"] = "5";
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertSame(96,$statuscheck->getSwapTagInt("hoursRemain"),"hours remaining is not what we expected");
        $this->assertSame(true,$statuscheck->getSwapTagBool("noticeLevelChanged"),"Notice level did not change!");

        // Recheck rental
        $rental = new Rental();
        $reply = $rental->loadByAvatarLink($avatar->getId());
        $this->assertSame(true, $reply->status, "Failed to load rental via avatar ".$avatar->getId().": ".$reply->message);
        $this->assertSame(11, $rental->getId(), "Incorrect rental ID found!");
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");
        $this->assertSame(2,$rental->getNoticeLink(),"Incorrect notice level assigned");
    }

    protected function setupRenewPost(string $target)
    {
        global $_POST, $system;
        $system->forceProcessURI("Renew/".$target);
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
        $storage = [
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
        $raw = time() ."Renew".$target . implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
    }

    protected function setupPostBuy(string $target)
    {
        global $_POST, $system;
        $system->forceProcessURI("Buy/".$target);
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
        $storage = [
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

