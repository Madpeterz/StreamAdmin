<?php

namespace StreamAdminR7;

use App\Endpoint\Secondlifeapi\Buy\Checkstock;
use App\Endpoint\Secondlifeapi\Buy\Getconfig;
use App\Endpoint\Secondlifeapi\Buy\Startrental;
use App\Models\Package;
use App\Models\Stream;
use PHPUnit\Framework\TestCase;

class SecondlifeApiBuy extends TestCase
{
    protected ?Package $package = null;
    public function test_MakeStreams()
    {
        global $_POST;
        $this->setupPost("Checkstock");
        $port = 5000;
        $loop = 0;
        $allOk = true;
        $why = "ok";
        while($loop < 5)
        {
            $stream = new Stream();
            $stream->setPackageLink($this->package->getId());
            $stream->setPort($port);
            $stream->setDjPassword("port".$port);
            $stream->setAdminPassword("none");
            $stream->setAdminUsername("user".$port);
            $stream->setMountpoint("/live");
            $stream->setNeedWork(0);
            $stream->setRentalLink(null);
            $stream->setServerLink(1);
            $stream->setStreamUid("p".$port);
            $reply = $stream->createEntry();
            if($reply->status == false) {
                $allOk = false;
                $why = $reply->message;
                break;
            }
            $port += 2;
            $loop++;
        }
        $this->assertSame("ok",$why,"Failed to create streams");
        $this->assertSame(true,$allOk,"Failed to create streams");
    }

    /**
     * @depends test_MakeStreams
     */
    public function test_Checkstock()
    {
        global $_POST;
        $this->setupPost("Checkstock");
        $_POST["packageuid"] = $this->package->getPackageUid();
        $checkstock = new Checkstock();
        $this->assertSame("ready",$checkstock->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$checkstock->getLoadOk(),"Load ok failed");
        $checkstock->process();
        $this->assertSame("ok",$checkstock->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$checkstock->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(true,$checkstock->getOutputObject()->getSwapTagBool("package_instock"),"Package needs to be in stock!".json_encode($checkstock->getOutputObject()->getSwapTagArray("debug")));
        $this->assertSame("289c3e36-69b3-40c5-9229-0c6a5d230766",$checkstock->getOutputObject()->getSwapTagString("texture_package_big"),"incorrect texture reply");
    }

    /**
     * @depends test_MakeStreams
     */
    public function test_Getconfig()
    {
        global $_POST;
        $this->setupPost("Getconfig");
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["texturepack"] = 1;
        $Getconfig = new Getconfig();
        $this->assertSame("ready",$Getconfig->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Getconfig->getLoadOk(),"Load ok failed");
        $Getconfig->process();
        $this->assertSame("ok",$Getconfig->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Getconfig->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(true,$Getconfig->getOutputObject()->getSwapTagBool("package_instock"),"Package needs to be in stock!");
        $this->assertSame(50,$Getconfig->getOutputObject()->getSwapTagInt("package_cost"),"Incorrect cost value");
        $this->assertSame("51d5f381-43cd-84f0-c226-f9f89c12af7e",$Getconfig->getOutputObject()->getSwapTagString("Texture-WaitOwner"),"Incorrect texture");
    }

    /**
     * @depends test_Getconfig
     */
    public function test_Startrental()
    {
        global $_POST;
        $this->setupPost("Startrental");
        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["amountpaid"] = $this->package->getCost() * 3;

        $startRental = new StartRental();
        $this->assertSame("ready",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Details should be with you shortly",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(0,$startRental->getOutputObject()->getSwapTagInt("owner_payment"),"incorrect owner payment");
        

        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["amountpaid"] = $this->package->getCost() + 4;

        $startRental = new StartRental();
        $this->assertSame("ready",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Payment amount not accepted",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(false,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as ok");

        
    }

    protected function setupPost(string $target)
    {
        global $_POST, $testsystem;
        $testsystem->forceProcessURI("Buy/".$target);
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
$_POST["version"] = "2.0.0.0";

$storage = [
            "version",
            "mode",
            "objectuuid",
            "regionname",
            "ownerkey",
            "ownername",
            "pos",
            "objectname",
            "objecttype",
        ];
        $real = [];
        foreach($storage as $valuename)
        {
            $real[] = $_POST[$valuename];
        }
        $_POST["unixtime"] = time();
        $raw = time()  . "Buy".$target. implode("",$real) . $testsystem->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $this->package = new Package();
        $this->package->loadID(1);
        $this->assertSame("UnitTestPackage",$this->package->getName(),"Test package not loaded");
    }
}
