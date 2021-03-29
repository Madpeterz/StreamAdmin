<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Stream\Create;
use App\Endpoint\SecondLifeApi\Apirequests\Next;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\R7\Model\Transactions;
use App\R7\Set\ApirequestsSet;
use App\R7\Set\DetailSet;
use App\R7\Set\TransactionsSet;
use PHPUnit\Framework\TestCase;

class Create_Test extends TestCase
{
    protected ?Server $server = null;

    public function test_Reconfig_server()
    {
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");
        $this->server->setApiLink(1);
        $this->server->setApiURL("asdasd".rand(1,4444));
        $update = $this->server->updateEntry();
        $this->assertSame("ok",$update["message"],"Invaild message state");
        $this->assertSame(true,$update["status"],"Unable to update server settings");
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");

        $this->server->setApiLink(2);
        $this->server->setApiURL("http://127.0.0.1/fake/centova.php");
        $this->server->setApiPassword("fake");
        $this->server->setApiServerStatus(true);
        $this->server->setApiSyncAccounts(true);
        $this->server->setOptPasswordReset(true);
        $this->server->setOptAutodjNext(true);
        $this->server->setOptToggleAutodj(true);
        $this->server->setOptToggleStatus(true);
        $this->server->setEventEnableStart(true);
        $this->server->setEventEnableRenew(true);
        $this->server->setEventDisableExpire(true);
        $this->server->setEventDisableRevoke(true);
        $this->server->setEventRevokeResetUsername(true);
        $this->server->setEventResetPasswordRevoke(true);
        $this->server->setEventClearDjs(true);
        $this->server->setEventRecreateRevoke(true);
        $this->server->setEventCreateStream(true);
        $this->server->setEventUpdateStream(true);
        $this->server->setEventStartSyncUsername(true);
        $update = $this->server->updateEntry();
        $this->assertSame("ok",$update["message"],"Invaild message state");
        $this->assertSame(true,$update["status"],"Unable to update server settings");
    }

    /**
     * @depends test_Reconfig_server
    */
    public function test_UI_create()
    {
        $apiRequests = new ApirequestsSet();
        $this->assertSame(true,$apiRequests->loadAll()["status"],"Status check failed");
        if($apiRequests->getCount() > 0) {
            $remove_status = $apiRequests->purgeCollection();
            $this->assertSame("ok",$remove_status["message"],"Status check failed");
            $this->assertSame(true,$remove_status["status"],"Status check failed");
            
        }
        $stream = new Stream();
        if($stream->loadByField("port",9998) == true)
        {
            if($stream->getRentalLink() != null) {
                $rentalid = $stream->getRentalLink();
                $stream->setRentalLink(null);
                $stream->updateEntry();
                $detail = new DetailSet();
                $detail->loadByField("rentalLink",$rentalid);
                if($detail->getCount() > 0) {
                    $detail->purgeCollection();
                }

                $rental = new Rental();
                $this->assertSame(true,$rental->loadID($rentalid),"Failed to load rental to clear");
                $this->assertSame(true,$rental->removeEntry()["status"],"Failed to remove rental");
            }
            $transactions = new TransactionsSet();
            $transactions->loadByField("streamLink",$stream->getId());
            if($transactions->getCount() > 0) {
                $purgestatus = $transactions->purgeCollection();
                $this->assertSame(true,$purgestatus["status"],"Failed to purge transactions");
            }
            $remove_status = $stream->removeEntry();
            $this->assertSame("ok",$remove_status["message"],"Status check failed");
            $this->assertSame(true,$remove_status["status"],"Status check failed");
            
        }
        $manageProcess = new Create();
        $_POST["port"] = 9998;
        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "/live";
        $_POST["originalAdminUsername"] = "MoreUnitTesting";
        $_POST["adminUsername"] = "MoreUnitTesting";
        $_POST["adminPassword"] = substr(md5(microtime()."a"),0,8);
        $_POST["djPassword"] = substr(md5(microtime()."b"),0,8);
        $_POST["needswork"] = 0;
        $_POST["apiConfigValue1"] = "";
        $_POST["apiConfigValue2"] = "";
        $_POST["apiConfigValue3"] = "";
        $_POST["api_create"] = 1;
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Stream creation underway",$statuscheck->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_Reconfig_server
    */
    public function test_FirstActionInQ()
    {
        $apiRequests = new ApirequestsSet();
        $this->assertSame(true,$apiRequests->loadAll()["status"],"Status check failed");
        $this->assertSame(1,$apiRequests->getCount(),"Incorrect number of requests in the Q");
    }

    /**
     * @depends test_FirstActionInQ
    */
    public function test_ActionLoops()
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Apirequests";
        $_POST["action"] = "Next";
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
        
        $exit = false;
        $loops=0;
        $expected_replys = [
            "ok",
            "passed",
            "none"
        ];
        while($exit == false)
        {
            $apiRequests = new ApirequestsSet();
            $status = $apiRequests->loadAll()["status"];
            $this->assertSame(true,$status,"Status check failed");
            if($status == false)
            {
                $exit = true;
                break;
            }
            if($apiRequests->getCount() == 0) {
                $exit = true;
                break;
            }
            $Next = new Next();
            $this->assertSame("Not processed",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
            $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
            $Next->process();
            $this->assertSame(true,in_array($Next->getOutputObject()->getSwapTagString("message"),$expected_replys),"incorrect reply on loop: ".$loops."");
            $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");
            if($Next->getOutputObject()->getSwapTagBool("status") == false)
            {
                $exit = true;
                break;
            }
            $loops++;
        }
        $this->assertSame(1,$loops,"Incorrect number of API steps");
    }
}
