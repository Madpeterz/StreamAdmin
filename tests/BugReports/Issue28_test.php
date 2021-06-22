<?php

namespace tests\BugReports;

use App\Endpoint\SecondLifeApi\Renew\Costandtime;
use App\Endpoint\SecondLifeApi\Renew\Details;
use App\Endpoint\SecondLifeApi\Renew\Renewnow;
use App\R7\Model\Avatar;
use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled;
use App\Endpoint\View\Import\Avatars;
use App\Endpoint\View\Import\Clients;
use App\Endpoint\View\Import\Packages;
use App\Endpoint\View\Import\Servers;
use App\Endpoint\View\Import\Streams;
use App\Endpoint\View\Import\Transactions;
use App\R7\Model\Rental;
use App\Endpoint\SecondLifeApi\Noticeserver\Next;

/*
https://github.com/Madpeterz/StreamAdmin/issues/28
Extra tests for the following cases

1: Check that after importing from R4 it has the correct notice level.
2: Check that after renewal the correct notice level is applyed.
3: Check that after notice service ticks the correct notice level is applyed.
*/
class Issue28 extends TestCase
{
    public function test_resetR4Dataset()
    {
        $sqlR4 = new MysqliEnabled();
        $openConnection = $sqlR4->sqlStartConnection("testsuser","testsuserPW","r4test",false,"127.0.0.1",10);
        $this->assertSame(true,$openConnection,"Unable to open SQL connection to create r4 test DB");
        $status = $sqlR4->rawSQL("tests/r4Issue28.sql");
        $this->assertSame(true,$status["status"],"Unable to import R4 database");
        $this->assertSame(true,$sqlR4->sqlSave(true),"Unable to save SQL dataset");
        $Servers = new Servers();
        $Servers->process();
        $statuscheck = $Servers->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 servers",$statuscheck,$missing);
        $Packages = new Packages();
        $Packages->process();
        $statuscheck = $Packages->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 packages",$statuscheck,$missing);
        $Avatars = new Avatars();
        $Avatars->process();
        $statuscheck = $Avatars->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 avatars",$statuscheck,$missing);
        $Streams = new Streams();
        $Streams->process();
        $statuscheck = $Streams->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 streams",$statuscheck,$missing);
        $Clients = new Clients();
        $Clients->process();
        $statuscheck = $Clients->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 clients",$statuscheck,$missing);
        $Clients = new Transactions();
        $Clients->process();
        $statuscheck = $Clients->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 transactions",$statuscheck,$missing); 
    }

    /**
     * @depends test_resetR4Dataset
    */
    public function test_ImportStreamsFromR4()
    {
        $avatars = new Avatar();
        $avatars->loadByAvatarName("Madpeter28 Zond28");
        $rental = new Rental();
        $rental->loadByAvatarLink($avatars->getId());
        $this->assertSame(10,$rental->getNoticeLink(),"Expected active rental - 
        if we are near 	Mon Jun 20 2033 then tests/r4Issue28.sql needs to be updated");
    }

    /**
     * @depends test_ImportStreamsFromR4
    */
    public function test_DetailedTestNoticeLevelIndex()
    {
        $Renewnow = new Renewnow();
        //  {"0":6,"5":5,"24":4,"72":3,"120":2,"168":1,"999":10}
        $test1 = $Renewnow->getNoticeLevelIndex(5);
        $this->assertSame(5,$test1,"Incorrect notice index");
        $test2 = $Renewnow->getNoticeLevelIndex(169);
        $this->assertSame(1,$test2,"Incorrect notice index");
    }

    /**
     * @depends test_DetailedTestNoticeLevelIndex
    */
    public function test_RenewAccountFromExpired()
    {
        $avatars = new Avatar();
        $avatars->loadByAvatarName("Madpeter28 Zond28");
        $rental = new Rental();
        $rental->loadByAvatarLink($avatars->getId());
        $rental->setExpireUnixtime(time()-420);
        $rental->setNoticeLink(6);
        $rental->updateEntry();
        $this->setupPost("Renewnow");
        $_POST["avatarUUID"] = "40000000-0000-0000-2800-000000000000";
        $Details = new Details();
        $Details->process();
        $dataset = $Details->getOutputObject()->getSwapTagArray("dataset");
        $split = explode("|||",$dataset[0]);
        $this->setupPost("Costandtime");
        $_POST["rentalUid"] = $split[0];
        $rentalOld = new Rental();
        $this->assertSame(true,$rentalOld->loadByField("rentalUid",$split[0]),"Unable to load rental before to check changes");
        $this->assertSame(0,$rentalOld->getRenewals(),"Renewals value is not zero as expected");
        $Costandtime = new Costandtime();
        $Costandtime->process();
        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["amountpaid"] = 444*3;
        $Renewnow = new Renewnow();
        $this->assertSame("Not processed",$Renewnow->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Renewnow->getLoadOk(),"Load ok failed");
        $Renewnow->process();
        $this->assertStringStartsWith(
            "Payment accepted there is now:",
            $Renewnow->getOutputObject()->getSwapTagString("message"),
            "Incorrect message"
        );
        $this->assertSame(true,$Renewnow->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $rentalNew = new Rental();
        $this->assertSame(true,$rentalNew->loadByField("rentalUid",$split[0]),"Unable to load rental after to check changes");
        $this->assertSame(3,$rentalNew->getRenewals(),"Renewals value did not change");
        $this->assertSame(10,$rentalNew->getNoticeLink(),"Notice level did not reset as expected");
    }

    /**
     * @depends test_RenewAccountFromExpired
    */
    public function test_ExpireAccountFromActiveViaNoticeService()
    {
        $avatars = new Avatar();
        $avatars->loadByAvatarName("Madpeter28 Zond28");
        $rental = new Rental();
        $rental->loadByAvatarLink($avatars->getId());
        $rental->setExpireUnixtime(time()-420);
        $rental->setNoticeLink(5);
        $rental->updateEntry();
        $this->setupPostNoticeServer("Next");
        $Next = new Next();
        $this->assertSame("Not processed",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $rentalNew = new Rental();
        $rentalNew->loadByAvatarLink($avatars->getId());
        $this->assertSame(6,$rentalNew->getNoticeLink(),"Notice level did not reset as expected");
    }

    protected function setupPostNoticeServer(string $target)
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Noticeserver";
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
    }

    protected function setupPost(string $target)
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Renew";
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
    }
}