<?php

namespace Tests\Control;

use App\Endpoint\Control\Client\Bulkremove;
use App\Endpoint\Control\Client\Create;
use App\Endpoint\Control\Client\Getnotecard;
use App\Endpoint\Control\Client\Message;
use App\Endpoint\Control\Client\Noticeoptout;
use App\Endpoint\Control\Client\Resend;
use App\Endpoint\Control\Client\Revoke;
use App\Endpoint\Control\Client\Update;
use App\Models\Avatar;
use App\Models\Detail;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Reseller;
use App\Models\Server;
use App\Models\Sets\DetailSet;
use App\Models\Sets\MessageSet;
use App\Models\Sets\RentalSet;
use App\Models\Staff;
use App\Models\Stream;
use Tests\TestWorker;

class ClientTest extends TestWorker
{
    public function test_Requires()
    {
        global $system;
        $reseller = new Reseller();
        $reseller->setAvatarLink(1);
        $reseller->setRate(100);
        $reply = $reseller->createEntry();
        $this->assertSame(true, $reply->status, "Failed to create reseller entry");
        $staff = new Staff();
        $staff->setOwnerLevel(true);
        $staff->setAvatarLink(1);
        $system->getSession()->attachStaffMember($staff);
        $server = new Server();
        $server->setDomain("https://testing.com");
        $server->setControlPanelURL("https://testing.com/cp");
        $server->setIpaddress("127.0.0.1");
        $server->setBandwidth(1000);
        $server->setBandwidthType("GB");
        $server->setTotalStorage(1000);
        $server->setTotalStorageType("TB");
        $replyServer = $server->createEntry();
        $this->assertSame(true, $replyServer->status, "Failed to create server");
        $package = new Package();
        $package->setPackageUid("asdvghas");
        $package->setName("testing");
        $package->setAutodj(false);
        $package->setListeners(10);
        $package->setTemplateLink(1);
        $package->setServertypeLink(1);
        $package->setCost(123);
        $package->setDays(7);
        $package->setTextureInstockSelected("51d5f381-43cd-84f0-c226-f9f89c12af7e");
        $package->setTextureInstockSmall("51d5f381-43cd-84f0-c226-f9f89c12af7e");
        $package->setTextureSoldout("51d5f381-43cd-84f0-c226-f9f89c12af7e");
        $package->setWelcomeNotecardLink(1);
        $package->setSetupNotecardLink(1);
        $package->setEnableGroupInvite(false);
        $package->setEnforceCustomMaxStreams(false);
        $package->setMaxStreamsInPackage(1);
        $replyPackage = $package->createEntry();
        $this->assertSame(true, $replyPackage->status, "Failed to create package");
        $stream = new Stream();
        $stream->setPackageLink($replyPackage->newId);
        $stream->setServerLink($replyServer->newId);
        $stream->setRentalLink(null);
        $stream->setPort(5500);
        $stream->setNeedWork(true);
        $stream->setAdminUsername("testing");
        $stream->setAdminPassword("testing");
        $stream->setDjPassword("testing");
        $stream->setStreamUid("testings");
        $stream->setMountpoint("/live");
        $replyStream = $stream->createEntry();
        $this->assertSame(true, $replyStream->status, "Failed to create stream");
    }
    /**
     * @depends test_Requires
     */
    public function test_Create()
    {
        $clientCreate = new Create();
        $_POST["avataruid"] = "System";
        $_POST["streamuid"] = "testings";
        $_POST["daysremaining"] = 31;
        $clientCreate->process();
        $reply = $clientCreate->getOutputObject();
        $this->assertSame("Client created", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
    /**
     * @depends test_Requires
     */
    public function test_GetNotecard()
    {
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        $clientGetNotecard = new Getnotecard();
        global $system;
        $system->setPage($rental->getRentalUid());
        $clientGetNotecard->process();
        $reply = $clientGetNotecard->getOutputObject();
        $this->assertSame($rental->getRentalUid(), $reply->getSwapTagString("rentaluid"), "expected reply to be for selected rental");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $this->assertStringContainsString("Listeners:", $reply->getSwapTagString("message"), "Message does not appear to be notecard");
    }
    /**
     * @depends test_Requires
     */
    public function test_Message()
    {
        $outbox = new MessageSet();
        $this->assertSame(0, $outbox->countInDB()->items, "Should not have any mail in outbox");
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        global $system;
        $system->setPage($rental->getRentalUid());
        $_POST["mail"] = "this is a test";

        $clientMessage = new Message();
        $clientMessage->process();
        $reply = $clientMessage->getOutputObject();
        $this->assertSame("Message added to outbox", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $outbox = new MessageSet();
        $this->assertSame(1, $outbox->countInDB()->items, "Should have only 1 message in outbox");
    }
    /**
     * @depends test_Requires
     */
    public function test_Resend()
    {
        $outbox = new DetailSet();
        $this->assertSame(0, $outbox->countInDB()->items, "Should not have any details requests pending");
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        global $system;
        $system->setPage($rental->getRentalUid());
        $clientresend = new Resend();
        $clientresend->process();
        $reply = $clientresend->getOutputObject();
        $this->assertSame(
            "Details request accepted, it should be with you shortly!",
            $reply->getSwapTagString("message"),
            "reply message not as expected"
        );
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $this->assertSame(1, $outbox->countInDB()->items, "should have 1 details requests pending");
    }
    /**
     * @depends test_Requires
     */
    public function test_AddOptOut()
    {
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        $_POST["add-optout-1"] = 1;
        $_POST["add-optout-3"] = 1;
        $_POST["add-optout-5"] = 1;
        global $system;
        $system->setPage($rental->getRentalUid());
        $clientOptout = new Noticeoptout();
        $clientOptout->process();
        $reply = $clientOptout->getOutputObject();
        $this->assertSame("Opt-outs updated enabled: 3 and removed 0", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
    /**
     * @depends test_AddOptOut
     */
    public function test_RemoveOptOut()
    {
        $this->resetPost();
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        $_POST["remove-optout-1"] = 1;
        global $system;
        $system->setPage($rental->getRentalUid());
        $clientOptout = new Noticeoptout();
        $clientOptout->process();
        $reply = $clientOptout->getOutputObject();
        $this->assertSame("Opt-outs updated enabled: 0 and removed 1", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
    /**
     * @depends test_RemoveOptOut
     */
    public function test_AddAnRemoveOptOut()
    {
        $this->resetPost();
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        $_POST["remove-optout-3"] = 1;
        $_POST["add-optout-2"] = 1;
        global $system;
        $system->setPage($rental->getRentalUid());
        $clientOptout = new Noticeoptout();
        $clientOptout->process();
        $reply = $clientOptout->getOutputObject();
        $this->assertSame("Opt-outs updated enabled: 1 and removed 1", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
    /**
     * @depends test_Requires
     */
    public function test_UpdateAddTimeLeft()
    {
        $this->resetPost();
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        global $system;
        $startunixtime = $rental->getExpireUnixtime();
        $system->setPage($rental->getRentalUid());
        $_POST["adjustment_days"] = 12;
        $_POST["adjustment_hours"] = 13;
        $_POST["adjustment_dir"] = true;
        $clientUpdate = new Update();
        $clientUpdate->process();
        $reply = $clientUpdate->getOutputObject();
        $this->assertStringContainsString("Adjusted timeleft", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $rental = new Rental();
        $rental->loadId(1);
        $moretime = false;
        if ($rental->getExpireUnixtime() > $startunixtime) {
            $moretime = true;
        }
        $this->assertTrue($moretime, "Expected rental time remaining to be higher old " . $startunixtime . " -> " . $rental->getExpireUnixtime());
    }
    /**
     * @depends test_Requires
     */
    public function test_UpdateRemoveTimeLeft()
    {
        $this->resetPost();
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        global $system;
        $startunixtime = $rental->getExpireUnixtime();
        $system->setPage($rental->getRentalUid());
        $_POST["adjustment_days"] = 5;
        $_POST["adjustment_hours"] = 1;
        $_POST["adjustment_dir"] = false;
        $clientUpdate = new Update();
        $clientUpdate->process();
        $reply = $clientUpdate->getOutputObject();
        $this->assertStringContainsString("Adjusted timeleft", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $rental = new Rental();
        $rental->loadId(1);
        $moretime = false;
        if ($rental->getExpireUnixtime() < $startunixtime) {
            $moretime = true;
        }
        $this->assertTrue($moretime, "Expected rental time remaining to be lower old " . $startunixtime . " -> " . $rental->getExpireUnixtime());
    }
    /**
     * @depends test_UpdateRemoveTimeLeft
     */
    public function test_UpdateMessage()
    {
        $this->resetPost();
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        global $system;
        $system->setPage($rental->getRentalUid());
        $_POST["message"] = "Hello world";
        $clientUpdate = new Update();
        $clientUpdate->process();
        $reply = $clientUpdate->getOutputObject();
        $this->assertStringContainsString("Message Updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame("Hello world", $rental->getMessage(), "Message not updated as expected");
    }
    /**
     * @depends test_UpdateMessage
     */
    public function test_UpdateTransfer()
    {
        $this->resetPost();
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame($rental->getAvatarLink(), 1, "rental owner is not as expected");
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        global $system;
        $system->setPage($rental->getRentalUid());
        $_POST["transfer_avataruid"] = "SysDevOp";
        $clientUpdate = new Update();
        $clientUpdate->process();
        $reply = $clientUpdate->getOutputObject();
        $this->assertStringContainsString("Message Updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame($rental->getAvatarLink(), 2, "New rental owner is not as expected");
    }
    /**
     * @depends test_UpdateTransfer
     */
    public function test_Revoke()
    {
        $detailsSet = new DetailSet();
        $detailsSet->loadAll();
        $reply = $detailsSet->purgeCollection();
        $this->assertSame(true, $reply->status, "Failed to remove pending details requests");
        $this->resetPost();
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        global $system;
        $system->setPage($rental->getRentalUid());
        $_POST["accept"] = "Accept";
        $clientRovoke = new Revoke();
        $clientRovoke->process();
        $reply = $clientRovoke->getOutputObject();
        $this->assertSame("Client rental revoked", $reply->getSwapTagString("message"), "Message does not appear to be correct");
    }
    /**
     * @depends test_Revoke
     */
    public function test_BulkRemove()
    {
        global $system;
        $reseller = new Reseller();
        $reseller->loadByAvatarLink(1);
        $staff = new Staff();
        $staff->setOwnerLevel(true);
        $staff->setAvatarLink(1);
        $system->getSession()->attachStaffMember($staff);
        $server = new Server();
        $server->setDomain("https://testing2.com");
        $server->setControlPanelURL("https://testing2.com/cp");
        $server->setIpaddress("127.0.0.2");
        $server->setBandwidth(1000);
        $server->setBandwidthType("GB");
        $server->setTotalStorage(1000);
        $server->setTotalStorageType("TB");
        $replyServer = $server->createEntry();
        $this->assertSame(true, $replyServer->status, "Failed to create server");
        $package = new Package();
        $package->setPackageUid("bulktest");
        $package->setName("testing2");
        $package->setAutodj(false);
        $package->setListeners(10);
        $package->setTemplateLink(1);
        $package->setServertypeLink(1);
        $package->setCost(123);
        $package->setDays(7);
        $package->setTextureInstockSelected("51d5f381-43cd-84f0-c226-f9f89c12af7e");
        $package->setTextureInstockSmall("51d5f381-43cd-84f0-c226-f9f89c12af7e");
        $package->setTextureSoldout("51d5f381-43cd-84f0-c226-f9f89c12af7e");
        $package->setWelcomeNotecardLink(1);
        $package->setSetupNotecardLink(1);
        $package->setEnableGroupInvite(false);
        $package->setEnforceCustomMaxStreams(false);
        $package->setMaxStreamsInPackage(1);
        $replyPackage = $package->createEntry();
        $this->assertSame(true, $replyPackage->status, "Failed to create package");

        // create test streams
        $startport = 5002;
        $counter = 0;
        $streamids = [];
        while ($counter < 50) {
            $stream = new Stream();
            $streamuid = $stream->createUID("streamUid", 8);
            $this->assertSame(true, $streamuid->status, "Failed to create stream uid");
            if ($streamuid->status == false) {
                break;
            }
            $stream->setPackageLink($replyPackage->newId);
            $stream->setServerLink($replyServer->newId);
            $stream->setRentalLink(null);
            $stream->setPort($startport + 2 + $counter);
            $stream->setNeedWork(true);
            $stream->setAdminUsername("testing");
            $stream->setAdminPassword("testing");
            $stream->setDjPassword("testing");
            $stream->setStreamUid($streamuid->uid);
            $stream->setMountpoint("/live");
            $replyStream = $stream->createEntry();
            $this->assertSame(true, $replyStream->status, "Failed to create needed stream: " . $replyStream->message);
            if ($replyStream->status == false) {
                $counter = 9999;
                break;
            }
            $streamids[] = $replyStream->newId;
            $counter += 2;
        }
        $avatar = new Avatar();
        $avatar->loadByAvatarUid("System");
        $this->assertSame(true, $avatar->isLoaded(), "Failed to load avatar");
        // create rentals
        $rentalids = [];
        $rentaluids = [];
        foreach ($streamids as $streamid) {
            $rental = new Rental();
            $uid = $rental->createUID("rentalUid", 8);
            $this->assertSame(true, $uid->status, "Failed to create uid for rental");
            if ($uid->status == false) {
                break;
            }
            $rental->setAvatarLink($avatar->getId());
            $rental->setStreamLink($streamid);
            $rental->setPackageLink($package->getId());
            $rental->setNoticeLink(6);
            $rental->setStartUnixtime(time() - 10);
            $rental->setExpireUnixtime(time() - 1);
            $rental->setRenewals(0);
            $rental->setTotalAmount($package->getCost());
            $rental->setMessage("Demo");
            $rental->setRentalUid($uid->uid);
            $create = $rental->createEntry();
            $this->assertSame(true, $create->status, "Failed to create rental");
            if ($create->status == false) {
                break;
            }
            $rentalids[] = $create->newId;
            $rentaluids[] = $uid->uid;
        }
        // ground work ready, now time to test
        $rentalSet = new RentalSet();
        $this->assertSame(25, $rentalSet->countInDB()->items, "Incorrect number of rentals found");
        $counter = 0;
        foreach ($rentaluids as $rentaluid) {
            $_POST["rental" . $rentaluid] = "purge";
            if (($counter % 2) == 0) {
                $_POST["rental" . $rentaluid] = "keep";
            }
            $counter++;
        }
        $bulkremove = new Bulkremove();
        $bulkremove->process();
        $reply = $bulkremove->getOutputObject();
        $this->assertSame("Removed 12 rentals! and skipped 13", $reply->getSwapTagString("message"), "Incorrect reply");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}
