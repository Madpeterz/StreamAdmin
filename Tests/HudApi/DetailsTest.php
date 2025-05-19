<?php

namespace Tests\HudApi;

use App\Endpoint\Control\Client\Create;
use App\Endpoint\Hudapi\Details\Resend;
use App\Endpoint\Control\Slconfig\Update;
use App\Endpoint\Hudapi\Rentals\Details;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Reseller;
use App\Models\Server;
use App\Models\Staff;
use App\Models\Stream;
use Tests\TestWorker;

class DetailsTest extends TestWorker
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
    public function test_HudRentalList()
    {
        $this->makeSLconnection(
            "Rentals","Details",
            "289c3e36-69b3-40c5-9229-0c6a5d230766","Madpeter Zond",
            "289c3e36-69b3-40c5-9229-0c6a5d230765","Example",
            "Unittest land","Hud");
        
        $rental = new Rental();
        $rental->loadId(1);
        $this->assertSame(true,$rental->isLoaded(),"Failed to load rental");
        $Details = new Details();
        $reply = $Details->getOutputObject();
        $this->assertSame("ready", $reply->getSwapTagString("message"), "object is not ready raw=".$_POST["raw"]);
        $Details->process();
        $reply = $Details->getOutputObject();
        
        $this->assertSame("Client account: Madpeter Zond", $reply->getSwapTagString("message"), "expected reply for message is not correct");
        $this->assertSame(1, $reply->getSwapTagInt("dataset_count"), "expected reply for dataset_count is not correct");
        $this->assertStringContainsString($rental->getRentalUid() . "|||5500",json_encode($reply->getSwapTagArray("dataset")),"expected reply for dataset is not correct");
        $this->assertSame(true,$reply->getSwapTagBool("status"), "incorrect status code");
    }
}
