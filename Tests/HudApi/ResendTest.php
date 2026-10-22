<?php

namespace Tests\HudApi;

use App\Endpoint\Control\Client\Create;
use App\Endpoint\Hudapi\Details\Resend;
use App\Endpoint\Control\Slconfig\Update;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Reseller;
use App\Models\Server;
use App\Models\Staff;
use App\Models\Stream;
use Tests\TestWorker;

class ResendTest extends TestWorker
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
        $clientCreate = new Create();
        $_POST["avataruid"] = "SysDevOp";
        $_POST["streamuid"] = "testings";
        $_POST["daysremaining"] = 31;
        $clientCreate->process();
        $reply = $clientCreate->getOutputObject();
        $this->assertSame("Client created", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $this->assertSame(true, $replyStream->status, "Failed to create stream");

        $_POST["newResellersRate"] = 40;
        $_POST["newResellers"] = 0;
        $_POST["owneravuid"] = "System";
        $_POST["ui_tweaks_clientsShowServer"] = 0;
        $_POST["ui_tweaks_groupStreamsBy"] = 0;
        $_POST["ui_tweaks_clients_fulllist"] = 1;
        $_POST["ui_tweaks_datatableItemsPerPage"] = 35;
        $_POST["displayTimezoneLink"] = 3;
        $_POST["eventsAPI"] = 1;
        $_POST["enableCoupons"] = 1;
        $_POST["ansSalt"] = "23142124123";
        $_POST["limitStreams"] = 1;
        $_POST["limitTime"] = 0;
        $_POST["maxStreamTimeDays"] = 7;
        $_POST["maxTotalStreams"] = 1;
        $_POST["hudAllowDiscord"] = 0;
        $_POST["hudDiscordLink"] = "https://google.com";
        $_POST["hudAllowGroup"] = 1;
        $_POST["hudGroupLink"] = "group join link";
        $_POST["hudAllowDetails"] = 1;
        $_POST["hudAllowRenewal"] = 1;

        $update = new Update();
        $update->process();
        $reply = $update->getOutputObject();
        $this->assertSame("System config updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Requires
     */
    public function test_HudResend()
    {
        $this->makeSLconnection(
            "Details","Resend",
            "289c3e36-69b3-40c5-9229-0c6a5d230766","Madpeter Zond",
            "289c3e36-69b3-40c5-9229-0c6a5d230765","Example",
            "Unittest land","Hud");
        
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");
        $_POST["rentalUid"] = $rental->_RentalUid;
        $Resend = new Resend();
        $reply = $Resend->getOutputObject();
        $this->assertSame("ready", $reply->getSwapTagString("message"), "object is not ready raw=".$_POST["raw"]);
        $Resend->process();
        $reply = $Resend->getOutputObject();
        $this->assertSame("Details request accepted, it should be with you shortly!", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
}
