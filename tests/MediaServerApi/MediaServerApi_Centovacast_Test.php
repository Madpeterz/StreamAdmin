<?php

namespace StreamAdminR7;

use App\Helpers\ServerApi\ServerApiHelper;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use PHPUnit\Framework\TestCase;

class MediaServerApi_Centovacast_Test extends TestCase
{
    protected ?Server $server = null;
    protected ?Stream $stream = null;
    protected ?Rental $rental = null;
    public function setUp(): void
    {
        $this->rental = new Rental();
        $this->assertSame(true,$this->rental->loadID(4),"Unable to load rental");
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID($this->rental->getStreamLink()),"Unable to load stream");
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID($this->stream->getServerLink()),"Unable to load server");
    }

    public function test_ConfigService(): void
    {
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
        $this->server->setEventCreateStream(true);
        $this->server->seteventStartSyncUsername(true);
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
        $this->assertSame("Reply from server: This is a faked reply for: systemSetstatus",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
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
        $this->assertSame("Reply from server: This is a faked reply for: serverReconfigure",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiPurgeDjs(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiPurgeDjs();
        $this->assertSame("Removed 0 dj accounts",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiDisableAccount(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiDisableAccount();
        $this->assertSame("Reply from server: This is a faked reply for: systemSetstatus",$ServerApiHelper->getMessage(),"Invaild message state");
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
        $this->assertSame("loaded",$status["message"],"Invaild message");
        $this->assertSame("Reply from server: This is a faked reply for: systemVersion",$ServerApiHelper->getMessage(),"invaild message");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiSetPasswords(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiResetPasswords();
        $this->assertSame("Reply from server: This is a faked reply for: serverReconfigure",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID($this->rental->getStreamLink()),"Unable to load stream");
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiSetPasswords("asdasd2","afasdf1");
        $this->assertSame("Reply from server: This is a faked reply for: serverReconfigure",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiStart(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiStart();
        $this->assertSame("Skipped server is already up",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiStop(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiStop();
        $this->assertSame("Reply from server: This is a faked reply for: serverStop",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiAutodjToggle(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiAutodjToggle();
        $this->assertSame("Reply from server: This is a faked reply for: serverSwitchsource",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiAutodjNext(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiAutodjNext();
        $this->assertSame("Reply from server: This is a faked reply for: serverNextsong",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_getAllAccounts(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->getAllAccounts();
        $this->assertSame("Reply from server: This is a faked reply for: systemListaccounts",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status["status"],"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_apiCustomizeUsername(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->apiCustomizeUsername();
        $this->assertSame("Reply from server: This is a faked reply for: serverStop",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventRecreateRevoke(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventRecreateRevoke();
        $this->assertSame("Reply from server: This is a faked reply for: systemSetstatus",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
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
        $this->assertSame("Removed 0 dj accounts",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventDisableExpire(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventDisableExpire();
        $this->assertSame("Reply from server: This is a faked reply for: systemSetstatus",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventDisableRevoke(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventDisableRevoke();
        $this->assertSame("Reply from server: This is a faked reply for: systemSetstatus",$ServerApiHelper->getMessage(),"Invaild message state");
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
        $this->assertSame("Reply from server: This is a faked reply for: serverReconfigure",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventStartSyncUsername(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventStartSyncUsername();
        $this->assertSame("Reply from server: This is a faked reply for: serverStop",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_eventRevokeResetUsername(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->eventRevokeResetUsername();
        $this->assertSame("Reply from server: This is a faked reply for: serverStop",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(false,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_optAutodjNext(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->optAutodjNext();
        $this->assertSame("Reply from server: This is a faked reply for: serverNextsong",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_optPasswordReset(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->optPasswordReset();
        $this->assertSame("Reply from server: This is a faked reply for: serverReconfigure",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }

    /**
     * @depends test_ConfigService
    */
    public function test_optToggleAutodj(): void
    {
        $ServerApiHelper = new ServerApiHelper($this->stream,true);
        $status = $ServerApiHelper->optToggleAutodj();
        $this->assertSame("Reply from server: This is a faked reply for: serverSwitchsource",$ServerApiHelper->getMessage(),"Invaild message state");
        $this->assertSame(true,$status,"incorrect status reply");
    }
}
