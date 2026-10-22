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
use App\Models\Set\DetailSet;
use App\Models\Set\MessageSet;
use App\Models\Set\RentalSet;
use App\Models\Staff;
use App\Models\Stream;
use Tests\TestWorker;

class ClientTest extends TestWorker
{
    public function test_Requires()
    {
        global $system;
        $reseller = new Reseller();
        $reseller->_AvatarLink = 1;
        $reseller->_Rate = 100;
        $reply = $reseller->createEntry();
        $this->assertSame(true, $reply->status, "Failed to create reseller entry");
        $staff = new Staff();
        $staff->_OwnerLevel = true;
        $staff->_AvatarLink = 1;
        $system->getSession()->attachStaffMember($staff);
        $server = new Server();
        $server->_Domain = "https://testing.com";
        $server->_ControlPanelURL = "https://testing.com/cp";
        $server->_Ipaddress = "127.0.0.1";
        $server->_Bandwidth = 1000;
        $server->_BandwidthType = "GB";
        $server->_TotalStorage = 1000;
        $server->_TotalStorageType = "TB";
        $replyServer = $server->createEntry();
        $this->assertSame(true, $replyServer->status, "Failed to create server");
        $package = new Package();
        $package->_PackageUid = "asdvghas";
        $package->_Name = "testing";
        $package->_Autodj = false;
        $package->_Listeners = 10;
        $package->_TemplateLink = 1;
        $package->_ServertypeLink = 1;
        $package->_Cost = 123;
        $package->_Days = 7;
        $package->_TextureInstockSelected = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $package->_TextureInstockSmall = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $package->_TextureSoldout = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $package->_WelcomeNotecardLink = 1;
        $package->_SetupNotecardLink = 1;
        $package->_EnableGroupInvite = false;
        $package->_EnforceCustomMaxStreams = false;
        $package->_MaxStreamsInPackage = 1;
        $replyPackage = $package->createEntry();
        $this->assertSame(true, $replyPackage->status, "Failed to create package");
        $stream = new Stream();
        $stream->_PackageLink = $replyPackage->newId;
        $stream->_ServerLink = $replyServer->newId;
        $stream->_RentalLink = null;
        $stream->_Port = 5500;
        $stream->_NeedWork = true;
        $stream->_AdminUsername = "testing";
        $stream->_AdminPassword = "testing";
        $stream->_DjPassword = "testing";
        $stream->_StreamUid = "testings";
        $stream->_Mountpoint = "/live";
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
        $system->setPage($rental->_RentalUid);
        $clientGetNotecard->process();
        $reply = $clientGetNotecard->getOutputObject();
        $this->assertSame($rental->_RentalUid, $reply->getSwapTagString("rentaluid"), "expected reply to be for selected rental");
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
        $system->setPage($rental->_RentalUid);
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
        $system->setPage($rental->_RentalUid);
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
        $system->setPage($rental->_RentalUid);
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
        $system->setPage($rental->_RentalUid);
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
        $system->setPage($rental->_RentalUid);
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
        $startunixtime = $rental->_ExpireUnixtime;
        $system->setPage($rental->_RentalUid);
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
        if ($rental->_ExpireUnixtime > $startunixtime) {
            $moretime = true;
        }
        $this->assertTrue($moretime, "Expected rental time remaining to be higher old " . $startunixtime . " -> " . $rental->_ExpireUnixtime);
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
        $startunixtime = $rental->_ExpireUnixtime;
        $system->setPage($rental->_RentalUid);
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
        if ($rental->_ExpireUnixtime < $startunixtime) {
            $moretime = true;
        }
        $this->assertTrue($moretime, "Expected rental time remaining to be lower old " . $startunixtime . " -> " . $rental->_ExpireUnixtime);
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
        $system->setPage($rental->_RentalUid);
        $_POST["message"] = "Hello world";
        $clientUpdate = new Update();
        $clientUpdate->process();
        $reply = $clientUpdate->getOutputObject();
        $this->assertStringContainsString("Message Updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame("Hello world", $rental->_Message, "Message not updated as expected");
    }
    /**
     * @depends test_UpdateMessage
     */
    public function test_UpdateTransfer()
    {
        $this->resetPost();
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame($rental->_AvatarLink, 1, "rental owner is not as expected");
        $this->assertSame(true, $rental->isLoaded(), "unable to load rental");
        global $system;
        $system->setPage($rental->_RentalUid);
        $_POST["transfer_avataruid"] = "SysDevOp";
        $clientUpdate = new Update();
        $clientUpdate->process();
        $reply = $clientUpdate->getOutputObject();
        $this->assertStringContainsString("Message Updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame($rental->_AvatarLink, 2, "New rental owner is not as expected");
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
        $system->setPage($rental->_RentalUid);
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
        $staff->_OwnerLevel = true;
        $staff->_AvatarLink = 1;
        $system->getSession()->attachStaffMember($staff);
        $server = new Server();
        $server->_Domain = "https://testing2.com";
        $server->_ControlPanelURL = "https://testing2.com/cp";
        $server->_Ipaddress = "127.0.0.2";
        $server->_Bandwidth = 1000;
        $server->_BandwidthType = "GB";
        $server->_TotalStorage = 1000;
        $server->_TotalStorageType = "TB";
        $replyServer = $server->createEntry();
        $this->assertSame(true, $replyServer->status, "Failed to create server");
        $package = new Package();
        $package->_PackageUid = "bulktest";
        $package->_Name = "testing2";
        $package->_Autodj = false;
        $package->_Listeners = 10;
        $package->_TemplateLink = 1;
        $package->_ServertypeLink = 1;
        $package->_Cost = 123;
        $package->_Days = 7;
        $package->_TextureInstockSelected = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $package->_TextureInstockSmall = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $package->_TextureSoldout = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $package->_WelcomeNotecardLink = 1;
        $package->_SetupNotecardLink = 1;
        $package->_EnableGroupInvite = false;
        $package->_EnforceCustomMaxStreams = false;
        $package->_MaxStreamsInPackage = 1;
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
            $stream->_PackageLink = $replyPackage->newId;
            $stream->_ServerLink = $replyServer->newId;
            $stream->_RentalLink = null;
            $stream->_Port = $startport + 2 + $counter;
            $stream->_NeedWork = true;
            $stream->_AdminUsername = "testing";
            $stream->_AdminPassword = "testing";
            $stream->_DjPassword = "testing";
            $stream->_StreamUid = $streamuid->uid;
            $stream->_Mountpoint = "/live";
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
            $rental->_AvatarLink = $avatar->_Id;
            $rental->_StreamLink = $streamid;
            $rental->_PackageLink = $package->_Id;
            $rental->_NoticeLink = 6;
            $rental->_StartUnixtime = time() - 10;
            $rental->_ExpireUnixtime = time() - 1;
            $rental->_Renewals = 0;
            $rental->_TotalAmount = $package->_Cost;
            $rental->_Message = "Demo";
            $rental->_RentalUid = $uid->uid;
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
