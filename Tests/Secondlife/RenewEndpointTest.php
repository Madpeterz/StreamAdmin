<?php

namespace Tests\Secondlife;

use App\Endpoint\Secondlifeapi\Renew\Details;
use App\Endpoint\Secondlifeapi\Buy\Startrental;
use App\Endpoint\Secondlifeapi\Renew\Costandtime;
use App\Endpoint\Secondlifeapi\Renew\Renewnow;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Reseller;
use App\Models\Server;
use App\Models\Stream;
use Tests\TestWorker;

class RenewEndpointTest extends TestWorker
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
        $package->_Days = 7;
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
    public function test_CostAndTime()
    {
        global $system;
        $system->setModule("Renew");
        $system->setArea("Costandtime");
        $this->slAPI();
        $rental = new Rental();
        $rental->loadID(1);
        $this->assertSame(true, $rental->isLoaded(), "Failed to load rental entry");
        $_POST["rentalUid"] = $rental->_RentalUid;
        $costandtime = new Costandtime();
        $costandtime->process();
        $reply = $costandtime->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertSame(123, $reply->getSwapTagInt("cost"), "expected cost should be 123");
        $this->assertSame("Week", $reply->getSwapTagString("word"), "expected cost should be 123");
    }
    /**
     * @depends test_CostAndTime
     */
    public function test_Details()
    {
        global $system;
        $system->setModule("Renew");
        $system->setArea("Details");
        $this->slAPI();
        $rental = new Rental();
        $rental->loadID(1);
        $this->assertSame(true, $rental->isLoaded(), "Failed to load rental entry");
        $_POST["avatarUUID"] =  "123e4567-e89b-12d3-a456-426614174000";
        $details = new Details();
        $reply = $details->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertSame("ready", $reply->getSwapTagString("message"), "Expected output should be 'ready'");
        $details->process();
        $reply = $details->getOutputObject();
        $this->assertStringContainsString("Client account:", $reply->getSwapTagString("message"), "Expected message to contain 'Client account:'");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status in reply");
        $this->assertSame(1, $reply->getSwapTagInt("dataset_count"), "Expected dataset_count to be 1");
        $this->assertStringContainsString($rental->_RentalUid, json_encode($reply->getSwapTagArray("dataset")), "Expected dataset to contain rental UID");
    }
    /**
     * @depends test_Details
     */
    public function test_RenewNow()
    {
        global $system;
        $system->setModule("Renew");
        $system->setArea("Renewnow");
        $this->slAPI();
        $rental = new Rental();
        $rental->loadID(1);
        $this->assertSame(true, $rental->isLoaded(), "Failed to load rental entry");
        $_POST["rentalUid"] = $rental->_RentalUid;
        $_POST["avatarUUID"] = "123e4567-e89b-12d3-a456-426614174000";
        $_POST["avatarName"] = "TestAvatar";
        $_POST["amountpaid"] = 123;
        $renewNow = new Renewnow();
        $reply = $renewNow->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertSame("ready", $reply->getSwapTagString("message"), "Expected output should be 'ready'");
        $renewNow->process();
        $reply = $renewNow->getOutputObject();
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status in reply");
        $this->assertStringStartsWith("Payment accepted there is now", $reply->getSwapTagString("message"), "Expected message to start with 'Payment accepted there is now'");
        $updatedRental = new Rental();
        $updatedRental->loadID(1);
        $this->assertGreaterThan($rental->_ExpireUnixtime, $updatedRental->_ExpireUnixtime, "Rental end time should be updated after renewal");
    }
}
