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
        $reseller->setAllowed(true);
        $reseller->setAvatarLink(1);
        $reseller->setRate(100);
        $create = $reseller->createEntry();
        $this->assertSame("ok", $create->message, "Failed to create reseller entry");
        $this->assertSame(true, $create->status, "Failed to create reseller entry");
        $server = new Server();
        $server->setBandwidth(1000);
        $server->setBandwidthType("Mbps");
        $server->setControlPanelURL("http://example.com/control");
        $server->setDomain("example.com");
        $server->setIpaddress("127.0.0.1");
        $server->setTotalStorage(50);
        $server->setTotalStorageType("TB");
        $create = $server->createEntry();
        $this->assertSame("ok", $create->message, "Failed to create server entry");
        $this->assertSame(true, $create->status, "Failed to create server entry");
        $this->assertSame(1, $server->getId(), "Server ID should be 1 after creation");
        $package = new Package();
        $package->setName('Test Package');
        $package->setAutodj(false);
        $package->setAutodjSize("0");
        $package->setBitrate(128);
        $package->setServertypeLink(1);
        $package->setSetupNotecardLink(1);
        $package->setCost(123);
        $package->setMaxStreamsInPackage(1);
        $package->setListeners(125);
        $package->setPackageUid("testing");
        $package->setTextureInstockSmall("small_texture");
        $package->setTextureInstockSelected("selected_texture");
        $package->setTextureSoldout("soldout_texture");
        $package->setTemplateLink(1);
        $create = $package->createEntry();
        $this->assertSame(true, $create->status, "Failed to create package entry");
        $this->assertSame("ok", $create->message, "Failed to create package entry");
        $this->assertSame(1, $package->getId(), "Package ID should be 1 after creation");
        $stream = new Stream();
        $stream->setServerLink(1);
        $stream->setPackageLink(1);
        $stream->setAdminPassword("admin123");
        $stream->setAdminUsername("admin");
        $stream->setDjPassword("dj123");
        $stream->setMountpoint("/live");
        $stream->setNeedWork(false);
        $stream->setStreamUid("str123");
        $stream->setPort(8000);
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
        $system->getSlConfig()->setEventsAPI(true);
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
        $rental->setExpireUnixtime(time()-120); // force expired
        $rental->setNoticeLink(10);
        $result = $rental->updateEntry();
        $this->assertSame(true,$result->status,"Failed to set expired time");
        $noticeNotecard = new Noticenotecard();
        $noticeNotecard->setName("magic");
        $result = $noticeNotecard->createEntry();
        $this->assertSame(true, $result->status, "failed to create notecard");
        $notice = new Notice();
        $notice->loadId(6);
        $this->assertSame(true,$notice->isLoaded(),"failed to load notice");
        $notice->setNoticeNotecardLink($noticeNotecard->getId());
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
