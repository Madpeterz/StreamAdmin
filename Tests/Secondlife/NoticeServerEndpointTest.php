<?php

namespace Tests\Secondlife;

use App\Endpoint\Secondlifeapi\Buy\Startrental;
use App\Endpoint\Secondlifeapi\Noticeserver\Next;
use App\Endpoint\Secondlifeapi\Noticeserver\Notecardmail;
use App\Endpoint\Secondlifeapi\Noticeserver\Updatenotecards;
use App\Models\Message;
use App\Models\Notice;
use App\Models\Noticenotecard;
use App\Models\Package;
use App\Models\Reseller;
use App\Models\Server;
use App\Models\Set\DetailSet;
use App\Models\Set\RentalSet;
use App\Models\Stream;
use Tests\HudApi\DetailsTest;
use Tests\TestWorker;

class NoticeServerEndpointTest extends TestWorker
{
    public function test_ReadyUp()
    {
        $reseller = new Reseller();
        $reseller->_Allowed = true;
        $reseller->_AvatarLink = 1;
        $reseller->_Rate = 100;
        $create = $reseller->createEntry();
        $this->assertSame("ok", $create->message, "Failed to create reseller entry");
        $this->assertSame(true, $create->status, "Failed to create reseller entry");
        $server = new Server();
        $server->_Bandwidth = 1000;
        $server->_BandwidthType = "Mbps";
        $server->_ControlPanelURL = "http://example.com/control";
        $server->_Domain = "example.com";
        $server->_Ipaddress = "127.0.0.1";
        $server->_TotalStorage = 50;
        $server->_TotalStorageType = "TB";
        $create = $server->createEntry();
        $this->assertSame("ok", $create->message, "Failed to create server entry");
        $this->assertSame(true, $create->status, "Failed to create server entry");
        $this->assertSame(1, $server->_Id, "Server ID should be 1 after creation");
        $package = new Package();
        $package->_Name = 'Test Package';
        $package->_Autodj = false;
        $package->_AutodjSize = "0";
        $package->_Bitrate = 128;
        $package->_ServertypeLink = 1;
        $package->_SetupNotecardLink = 1;
        $package->_Cost = 123;
        $package->_MaxStreamsInPackage = 1;
        $package->_Listeners = 125;
        $package->_PackageUid = "testing";
        $package->_TextureInstockSmall = "small_texture";
        $package->_TextureInstockSelected = "selected_texture";
        $package->_TextureSoldout = "soldout_texture";
        $package->_TemplateLink = 1;
        $create = $package->createEntry();
        $this->assertSame(true, $create->status, "Failed to create package entry");
        $this->assertSame("ok", $create->message, "Failed to create package entry");
        $this->assertSame(1, $package->_Id, "Package ID should be 1 after creation");
        $stream = new Stream();
        $stream->_ServerLink = 1;
        $stream->_PackageLink = 1;
        $stream->_AdminPassword = "admin123";
        $stream->_AdminUsername = "admin";
        $stream->_DjPassword = "dj123";
        $stream->_Mountpoint = "/live";
        $stream->_NeedWork = false;
        $stream->_StreamUid = "str123";
        $stream->_Port = 8000;
        $create = $stream->createEntry();
        $this->assertSame("ok", $create->message, "Failed to create stream entry");
        $this->assertSame(true, $create->status, "Failed to create stream entry");
   }
    /**
     * @depends test_ReadyUp
     */
    public function test_Startrental()
    {
        global $system;
        $system->getSlConfig()->_EventsAPI = true;
        $result = $system->getSlConfig()->updateEntry();
        $this->assertSame(true, $result->status, "Failed to update SL config for Events API");
        
        $system->setModule("Client");
        $system->setArea("Startrental");
        $this->slAPI();
        $startRental = new Startrental();
        $status = $startRental->getOutputObject()->addSwapTagString("message");
        $this->assertSame("ready", $status, "Startrental should have 'ready' status before processing");
        $_POST["packageuid"] = "testing";
        $_POST["avatarUUID"] = "123e4567-e89b-12d3-a456-426614174000";
        $_POST["avatarName"] = "TestAvatar";
        $_POST["amountpaid"]= 123;
        $startRental->process();
        $reply = $startRental->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertSame("Details should be with you shortly", $reply->getSwapTagString("message"), "Expected output should be 'ok'");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status in reply");
        $this->assertSame(false, $reply->getSwapTagBool("owner_payment"), "Expected owner_payment to be set to false");
    }
    /**
     * @depends test_Startrental
     */
    public function test_Next()
    {
        $details = new DetailSet();
        $details->loadAll();
        $result = $details->purgeCollection();
        $this->assertSame(true,$result->status,"Expected to purge collection");
        $rental = new RentalSet();
        $result = $rental->loadAll();
        $this->assertSame(true,$result->status,"Failed to load rentals");
        $rental = $rental->getFirst();
        $rental->_ExpireUnixtime = time()-120; // force expired
        $rental->_NoticeLink = 10;
        $result = $rental->updateEntry();
        $this->assertSame(true,$result->status,"Failed to set expired time");
        $noticeNotecard = new Noticenotecard();
        $noticeNotecard->_Name = "magic";
        $result = $noticeNotecard->createEntry();
        $this->assertSame(true, $result->status, "failed to create notecard");
        $notice = new Notice();
        $notice->loadId(6);
        $this->assertSame(true,$notice->isLoaded(),"failed to load notice");
        $notice->_NoticeNotecardLink = $noticeNotecard->_Id;
        $status = $notice->updateEntry();
        $this->assertSame(true,$status->status,"Failed to update notice");
        $next = new Next();
        $status = $next->getOutputObject()->addSwapTagString("message");
        $this->assertSame("ready", $status, "Next should have 'ready' status before processing");
        $next->process();
        $reply = $next->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertSame("ok", $reply->getSwapTagString("message"), "Expected output should be 'ok'");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status in reply");
    }
    /**
     * @depends test_Next
     */
    public function test_Updatenotecards()
    {
        $Updatenotecards = new Updatenotecards();
        $status = $Updatenotecards->getOutputObject()->addSwapTagString("message");
        $this->assertSame("ready", $status, "nextNotecardmail should have 'ready' status before processing");
        $_POST["notecards"] = "none";
        $Updatenotecards->process();
        $reply = $Updatenotecards->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertStringStartsWith("ok", $reply->getSwapTagString("message"), "Expected output should be 'ok'");
    }
}
