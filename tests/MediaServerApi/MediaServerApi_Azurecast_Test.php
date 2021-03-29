<?php

namespace StreamAdminR7;

use App\Helpers\ServerApi\ServerApiHelper;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use PHPUnit\Framework\TestCase;

class MediaServerApi_Azurecast_Test extends TestCase
{
    protected ?Server $server = null;
    protected ?Stream $stream = null;
    protected ?Rental $rental = null;
    public function setUp(): void
    {
        $this->rental = new Rental();
        $this->assertSame(true,$this->rental->loadID(3),"Unable to load rental");
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID($this->rental->getStreamLink()),"Unable to load stream");
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID($this->stream->getServerLink()),"Unable to load server");
    }

    public function test_ConfigService(): void
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
        $this->server->setApiLink(5);
        $update = $this->server->updateEntry();
        if($update["message"] != "No changes made")
        {
            $this->assertSame("ok",$update["message"],"Unable to update server settings");
            $this->assertSame(true,$update["status"],"Unable to update server settings");
        }
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID($this->rental->getStreamLink()),"Unable to load stream");
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID($this->stream->getServerLink()),"Unable to load server");
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
        $this->assertSame("ok",$update["message"],"Invaild message state");
        $this->assertSame(true,$update["status"],"Unable to update server settings");
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID($this->stream->getServerLink()),"Unable to load server");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiRecreateAccount(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $this->assertSame("Avatar loaded",$ServerApiHelper->getMessage(),"Unable to auto load avatar");
        $status = $ServerApiHelper->apiRecreateAccount();
        $this->assertSame("Skipped terminateAccount not supported on this api",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiEnableAccount(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiEnableAccount();
        $this->assertSame("No change needed",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiChangeTitle(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiChangeTitle();
        $this->assertSame("Calling change title now",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiPurgeDjs(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiPurgeDjs();
        $this->assertSame("Removed 2 dj accounts",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiDisableAccount(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiDisableAccount();
        $this->assertSame("Account: susspended",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiServerStatus(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiServerStatus();
        $this->assertSame(true,is_array($status),"Expected an array but got not an array");
        $this->assertSame(true,$status["status"],"Invaild status");
        $this->assertSame("Limited reply",$status["message"],"Invaild message");
        $this->assertSame("Passed callable action checks",$ServerApiHelper->getMessage(),"invaild message");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiSetPasswords(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiResetPasswords();
        $this->assertSame("Password change request received",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID($this->rental->getStreamLink()),"Unable to load stream");
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiSetPasswords("asdasd2","afasdf1");
        $this->assertSame("Password change request received",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiStart(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiStart();
        $this->assertSame("Server and AutoDJ started",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiStop(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiStop();
        $this->assertSame("server stopped",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiAutodjToggle(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiAutodjToggle();
        $this->assertSame("Toggled auto DJ to: stop",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiAutodjNext(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiAutodjNext();
        $this->assertSame("Skip accepted",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_getAllAccounts(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->getAllAccounts();
        $this->assertSame("Got account list",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status["status"],"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiCustomizeUsername(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiCustomizeUsername();
        $this->assertSame("API flag eventStartSyncUsername disallowed by api_config",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventRecreateRevoke(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventRecreateRevoke();
        $this->assertSame("Skipped terminateAccount not supported on this api",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventEnableStart(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventEnableStart();
        $this->assertSame("No change needed",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventClearDjs(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventClearDjs();
        $this->assertSame("Removed 2 dj accounts",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventDisableExpire(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventDisableExpire();
        $this->assertSame("Account: susspended",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventDisableRevoke(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventDisableRevoke();
        $this->assertSame("Account: susspended",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventEnableRenew(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventEnableRenew();
        $this->assertSame("No change needed",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventResetPasswordRevoke(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventResetPasswordRevoke();
        $this->assertSame("Password change request received",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventStartSyncUsername(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventStartSyncUsername();
        $this->assertSame("API flag eventStartSyncUsername disallowed by api_config",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventRevokeResetUsername(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventRevokeResetUsername();
        $this->assertSame("API flag eventStartSyncUsername disallowed by api_config",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_optAutodjNext(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->optAutodjNext();
        $this->assertSame("Skip accepted",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_optPasswordReset(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->optPasswordReset();
        $this->assertSame("Password change request received",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_optToggleAutodj(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->optToggleAutodj();
        $this->assertSame("Toggled auto DJ to: stop",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }
}
