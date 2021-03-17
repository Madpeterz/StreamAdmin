<?php

namespace StreamAdminR7;

use App\MediaServer\MediaCp;
use App\R7\Model\Package;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use tests\MediaServer\TestingFramework;

class MediaCp_Test extends TestingFramework
{
    protected function setUp(): void
    {
        $this->server = new Server();
        $this->assertSame(true,$this->server->loadID(1),"Unable to load server");
        $this->stream = new Stream();
        $this->assertSame(true,$this->stream->loadID(13),"Unable to load stream");
        $this->package = new Package();
        $this->assertSame(true,$this->package->loadID(1),"Unable to load package");
        $this->api = new MediaCp($this->stream,$this->server,$this->package);
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
        $this->server->setApiLink(3);
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
}
