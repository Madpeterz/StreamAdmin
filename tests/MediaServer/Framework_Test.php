<?php

namespace tests\MediaServer;

use App\MediaServer\Abstracts\PublicApi;
use App\MediaServer\None;
use App\R7\Model\Package;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use PHPUnit\Framework\TestCase;

abstract class Framework_Test extends TestCase
{
    protected ?Server $server = null;
    protected ?Stream $stream = null;
    protected ?Package $package = null;
    protected ?PublicApi $api = null;
    protected function setUp(): void
    {
    }
    public function test_AdjustServerConfig()
    {
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_serverStatus()
    {
        $status = $this->api->serverStatus();
        $this->assertSame("This api does not support server status",$status["message"],"incorrect API reply");
        $this->assertSame(false,$status["status"],"Bad status reply");
    }


    /**
     * @depends test_AdjustServerConfig
    */
    public function test_Create()
    {
        $status = $this->api->recreateAccount();
        $this->assertSame("Skipped create_account not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad status reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_setAccountState()
    {
        $status = $this->api->setAccountState(false);
        $this->assertSame("Unable to get account state",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(false,$status,"Bad status reply");
        $status = $this->api->setAccountState(true);
        $this->assertSame("Unable to get account state",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(false,$status,"Bad status reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_djList()
    {
        $status = $this->api->djList();
        $this->assertSame("Skipped dj_list not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,is_array($status),"Bad reply");
        $this->assertSame(true,$status["status"],"expected a true status reply");
        $this->assertSame(0,count($status["list"]),"expected zero entrys but got some anyway");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_purgeDjAccount()
    {
        $status = $this->api->purgeDjAccount("fake");
        $this->assertSame("Skipped remove_dj not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_streamState()
    {
        $status = $this->api->streamState();
        $this->assertSame("Server appears to be down",$status["message"],"expected a vaild reply");
        $this->assertSame("Skipped stream_state not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,is_array($status),"Bad reply");
        $this->assertSame(false,$status["status"],"expected a vaild reply");
        $this->assertSame(false,$status["state"],"expected enabled");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_removeAccount()
    {
        $status = $this->api->removeAccount("fake");
        $this->assertSame("Skipped terminateAccount not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_recreateAccount()
    {
        $status = $this->api->recreateAccount();
        $this->assertSame("Skipped create_account not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_accountNameList()
    {
        $status = $this->api->accountNameList(true);
        $this->assertSame("Skipped accountNameList not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(false,$status["status"],"Bad reply");
        $this->assertSame(true,is_array($status["usernames"]),"Bad reply");
        $this->assertSame(true,is_array($status["passwords"]),"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optAutodjNext()
    {
        $status = $this->api->optAutodjNext();
        $this->assertSame("Skipped not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optToggleAutodj()
    {
        $status = $this->api->optToggleAutodj();
        $this->assertSame("Skipped not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optToggleStatusj()
    {
        $status = $this->api->optToggleStatus(false);
        $this->assertSame("Skipped not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
        $status = $this->api->optToggleStatus(true);
        $this->assertSame("Skipped not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_getAccountState()
    {
        $status = $this->api->getAccountState();
        $this->assertSame("Skipped account_state not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(false,$status["status"],"Bad reply");
        $this->assertSame(false,$status["state"],"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_optPasswordReset()
    {
        $status = $this->api->optPasswordReset();
        $this->assertSame("Skipped not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
    }

    /**
     * @depends test_AdjustServerConfig
    */
    public function test_changeTitle()
    {
        $status = $this->api->changeTitle("fake");
        $this->assertSame("Skipped not supported on this api",$this->api->getLastApiMessage(),"incorrect API reply");
        $this->assertSame(true,$status,"Bad reply");
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
