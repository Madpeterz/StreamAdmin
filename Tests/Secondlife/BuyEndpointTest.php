<?php

namespace Tests\Secondlife;

use App\Endpoint\Secondlifeapi\Buy\Checkstock;
use App\Endpoint\Secondlifeapi\Buy\Getconfig;
use App\Endpoint\Secondlifeapi\Buy\Startrental;
use App\Models\Package;
use App\Models\Reseller;
use App\Models\Server;
use App\Models\Stream;
use Tests\TestWorker;

class BuyEndpointTest extends TestWorker
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
    public function test_Getconfig()
    {
        $Getconfig = new Getconfig();
        $Getconfig->setOwnerOverride(true);
        $_POST["packageuid"] = "testing";
        $_POST["texturepack"] = 1;
        $Getconfig->process();
        $reply = $Getconfig->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertSame("ok", $reply->getSwapTagString("message"), "Expected output should be 'ok'");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status in reply");
        $this->assertSame("small_texture", $reply->getSwapTagString("Texture-PackageSmall"), "Expected Texture-PackageSmall to match");
        $this->assertSame("System owner mode", $reply->getSwapTagString("reseller_mode"), "Expected mode to be 'System owner mode'");
        $this->assertSame(123, $reply->getSwapTagInt("package_cost"), "Expected package cost to be 132");
        
    }
    /**
     * @depends test_Getconfig
     */
    public function test_Checkstock()
    {
        $checkstock = new Checkstock();
        $checkstock->setOwnerOverride(true);
        $_POST["packageuid"] = "testing";
        $checkstock->process();
        $reply = $checkstock->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertSame("ok", $reply->getSwapTagString("message"), "Expected output should be 'ok'");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status in reply");
        $this->assertSame(true, $reply->getSwapTagBool("package_instock"), "Expected package_instock to be true");
        $this->assertSame(123, $reply->getSwapTagInt("package_cost"), "Expected package cost to be 123");
    }
    /**
     * @depends test_Checkstock
     */
    public function test_Startrental()
    {
        global $system;
        $system->setModule("Buy");
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


}
