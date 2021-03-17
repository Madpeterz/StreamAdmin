<?php

namespace StreamAdminR7;

use App\MediaServer\Azurecast;
use App\R7\Model\Package;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use tests\MediaServer\TestingFramework;

class Azurecast_Test extends TestingFramework
{
    protected function setUp(): void
    {
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID(13),"Unable to load stream");
        $this->package = new Package();
        $this->assertSame(true,$this->package->loadID(1),"Unable to load package");
        $this->api = new Azurecast($this->stream,$this->server,$this->package);
    }
    public function test_AdjustServerConfig()
    {
        $this->stream->setApiConfigValue1(rand(1,4000));
        $this->stream->setApiConfigValue2(rand(1,4000));
        $this->stream->setApiConfigValue3(rand(1,4000));
        $update = $this->stream->updateEntry();
        if($update["message"] != "No changes made")
        {
            $this->assertSame("ok",$update["message"],"Unable to update stream settings");
            $this->assertSame(true,$update["status"],"Unable to update stream settings");
        }
        $apply = $this->server->setApiLink(5);
        $update = $this->server->updateEntry();
        if($update["message"] != "No changes made")
        {
            $this->assertSame("ok",$update["message"],"Unable to update server settings");
            $this->assertSame(true,$update["status"],"Unable to update server settings");
        }
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID(13),"Unable to load stream");
        $this->server->setApiLink(6);
        $this->server->setApiURL("http://127.0.0.1/fake/azurecast.php/");
        $this->server->setApiPassword("faked");
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
        $update = $this->server->updateEntry();
        $this->assertSame("ok",$update["message"],"Unable to update server settings");
        $this->assertSame(true,$update["status"],"Unable to update server settings");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_serverStatus()
    {
        $status = $this->api->serverStatus();
        $this->assertSame("Limited reply",$status["message"],"incorrect API reply");
        $this->assertSame(true,$status["status"],"Bad status reply");
    }


    /**
     * @depends test_AdjustServerConfig
    */
    public function test_Create()
    {
        $status = $this->api->recreateAccount();
        $this->assertSame("Skipped createAccount not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(false,$status,"Bad status reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_setAccountState()
    {
        $status = $this->api->setAccountState(false);
        $this->assertSame("Account: susspended",$this->api->getLastApiMessage(),"incorrect API reply");
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
        $this->assertSame("fetched DJ list",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,is_array($status),"Bad reply");
        $this->assertSame(true,$status["status"],"expected a true status reply");
        $this->assertSame(2,count($status["list"]),"expected two entrys but got zero");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_purgeDjAccount()
    {
        $status = $this->api->purgeDjAccount("dj_test");
        $this->assertSame("DJ removed",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_streamState()
    {
        $status = $this->api->streamState();
        $this->assertSame("stream up/auto dj up",$status["message"],"expected a vaild reply");
        $this->assertSame("stream up/auto dj up",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,is_array($status),"Bad reply");
        $this->assertSame(true,$status["status"],"expected a vaild reply");
        $this->assertSame(true,$status["state"],"expected enabled");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_removeAccount()
    {
        $status = $this->api->removeAccount();
        $this->assertSame("Skipped terminateAccount not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(false,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_recreateAccount()
    {
        $status = $this->api->recreateAccount();
        $this->assertSame("Skipped createAccount not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(false,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_accountNameList()
    {
        $status = $this->api->accountNameList(true);
        $this->assertSame("Got account list",$this->api->getLastApiMessage(),"incorrect API reply");
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
        $this->assertSame("Skip accepted",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optToggleAutodj()
    {
        $status = $this->api->optToggleAutodj();
        $this->assertSame("Toggled auto DJ to: stop",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optToggleStatusj()
    {
        $status = $this->api->optToggleStatus(false);
        $this->assertSame("server stopped",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
        $status = $this->api->optToggleStatus(true);
        $this->assertSame("Server and AutoDJ started",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_getAccountState()
    {
        $status = $this->api->getAccountState();
        $this->assertSame("got account state",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status["status"],"Bad reply");
        $this->assertSame(true,$status["state"],"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optPasswordReset()
    {
        $status = $this->api->optPasswordReset();
        $this->assertSame("Password change request received",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_changeTitle()
    {
        $status = $this->api->changeTitle("fake");
        $this->assertSame("Skipped changeTitle not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(false,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_eventStartSyncUsername()
    {
        $status = $this->api->eventStartSyncUsername("oldusername");
        $this->assertSame("Skipped not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }
    
}
