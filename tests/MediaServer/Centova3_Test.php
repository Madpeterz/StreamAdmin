<?php

namespace StreamAdminR7;

use App\MediaServer\Centova3;
use App\R7\Model\Package;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use tests\MediaServer\TestingFramework;

class Centova3_Test extends TestingFramework
{
    protected function setUp(): void
    {
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID(8),"Unable to load stream");
        $this->package = new Package();
        $this->assertSame(true,$this->package->loadID(1),"Unable to load package");
        $this->api = new Centova3($this->stream,$this->server,$this->package);
    }
    public function test_AdjustServerConfig()
    {
        $this->server->setApiLink(5);
        $update = $this->server->updateEntry();
        if($update["message"] != "No changes made")
        {
            $this->assertSame("ok",$update["message"],"Unable to update server settings");
            $this->assertSame(true,$update["status"],"Unable to update server settings");
        }
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
        $this->server->setEventDisableExpire(true);
        $this->server->setEventDisableRevoke(true);
        $this->server->setEventRevokeResetUsername(true);
        $this->server->setEventResetPasswordRevoke(true);
        $this->server->setEventClearDjs(true);
        $this->server->setEventRecreateRevoke(true);
        $this->server->getEventCreateStream(true);
        $this->assertSame(true,$this->server->updateEntry()["status"],"Unable to update server settings");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_serverStatus()
    {
        $status = $this->api->serverStatus();
        $this->assertSame("Reply from server: This is a faked reply for: systemVersion",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status["status"],"Bad status reply");
    }


    /**
     * @depends test_AdjustServerConfig
    */
    public function test_Create()
    {
        $status = $this->api->recreateAccount();
        $this->assertSame("Reply from server: This is a faked reply for: systemSetstatus",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad status reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_setAccountState()
    {
        $status = $this->api->setAccountState(false);
        $this->assertSame("Reply from server: This is a faked reply for: systemSetstatus",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad status reply");
        $status = $this->api->setAccountState(true);
        $this->assertSame("No change needed",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad status reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_djList()
    {
        $status = $this->api->djList();
        $loop = 0;
        while($loop < 10)
        {
            $message = $this->api->getLastApiMessage();
            if($message == "Reply from server: Invalid argument supplied for foreach()") {
                $message = "ok";
            } elseif($message == "No DJ accounts") {
                $message = "ok";
            }
            $this->assertSame("ok",$message,"incorrect API reply");
            $this->assertSame(true,is_array($status),"Bad reply");
            $this->assertSame(true,$status["status"],"expected a true status reply");
            $this->assertSame(0,count($status["list"]),"expected zero entrys but got some anyway");
            $loop++;
        }
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_purgeDjAccount()
    {
        $status = $this->api->purgeDjAccount("fake");
        $this->assertSame("Reply from server: This is a faked reply for: serverManagedj",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_streamState()
    {
        $status = $this->api->streamState();
        $this->assertSame("ok",$status["message"],"expected a vaild reply");
        $this->assertSame("DJ connected",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,is_array($status),"Bad reply");
        $this->assertSame(true,$status["status"],"expected a vaild reply");
        $this->assertSame(true,$status["state"],"expected enabled");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_removeAccount()
    {
        $status = $this->api->removeAccount("fake");
        $this->assertSame("Reply from server: This is a faked reply for: systemTerminate",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_recreateAccount()
    {
        $status = $this->api->recreateAccount();
        $this->assertSame("Reply from server: This is a faked reply for: systemSetstatus",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_accountNameList()
    {
        $status = $this->api->accountNameList(true);
        $this->assertSame("Reply from server: This is a faked reply for: serverGetaccount",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status["status"],"Bad reply");
        $this->assertSame(true,is_array($status["usernames"]),"Bad reply");
        $this->assertSame(true,is_array($status["passwords"]),"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optAutodjNext()
    {
        $status = $this->api->optAutodjNext();
        $this->assertSame("Reply from server: This is a faked reply for: serverNextsong",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optToggleAutodj()
    {
        $status = $this->api->optToggleAutodj();
        $this->assertSame("Reply from server: This is a faked reply for: serverSwitchsource",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optToggleStatusj()
    {
        $status = $this->api->optToggleStatus(false);
        $this->assertSame("Reply from server: This is a faked reply for: serverStop",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
        $status = $this->api->optToggleStatus(true);
        $this->assertSame("Skipped server is already up",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_getAccountState()
    {
        $status = $this->api->getAccountState();
        $this->assertSame("Reply from server: This is a faked reply for: serverGetaccount",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status["status"],"Bad reply");
        $this->assertSame(true,$status["state"],"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optPasswordReset()
    {
        $status = $this->api->optPasswordReset();
        $this->assertSame("Reply from server: This is a faked reply for: serverReconfigure",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_changeTitle()
    {
        $status = $this->api->changeTitle("fake");
        $this->assertSame("Reply from server: This is a faked reply for: serverReconfigure",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_eventStartSyncUsername()
    {
        $status = $this->api->eventStartSyncUsername("oldusername");
        $this->assertSame("Reply from server: This is a faked reply for: systemRename",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }
    
}
