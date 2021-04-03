<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Buy\Checkstock;
use App\Endpoint\SecondLifeApi\Buy\Getconfig;
use App\Endpoint\SecondLifeApi\Buy\Startrental;
use App\R7\Model\Package;
use App\R7\Set\PackageSet;
use PHPUnit\Framework\TestCase;

class SecondlifeApiBuy extends TestCase
{
    protected $package = null;
    public function test_Checkstock()
    {
        $this->setupPost("Checkstock");

        $_POST["packageuid"] = $this->package->getPackageUid();
        $checkstock = new Checkstock();
        $this->assertSame("Not processed",$checkstock->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$checkstock->getLoadOk(),"Load ok failed");
        $checkstock->process();
        $this->assertSame("ok",$checkstock->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$checkstock->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(true,$checkstock->getOutputObject()->getSwapTagBool("package_instock"),"Package needs to be in stock!");
        $this->assertSame("289c3e36-69b3-40c5-9229-0c6a5d230766",$checkstock->getOutputObject()->getSwapTagString("texture_package_big"),"incorrect texture reply");
    }

    public function test_Getconfig()
    {
        $this->setupPost("Getconfig");

        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["texturepack"] = 1;
        $Getconfig = new Getconfig();
        $this->assertSame("Not processed",$Getconfig->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Getconfig->getLoadOk(),"Load ok failed");
        $Getconfig->process();
        $this->assertSame("ok",$Getconfig->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Getconfig->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(true,$Getconfig->getOutputObject()->getSwapTagBool("package_instock"),"Package needs to be in stock!");
        $this->assertSame(50,$Getconfig->getOutputObject()->getSwapTagInt("package_cost"),"Incorrect cost value");
        $this->assertSame("51d5f381-43cd-84f0-c226-f9f89c12af7e",$Getconfig->getOutputObject()->getSwapTagString("texture_waitingforowner"),"Incorrect texture");
    }

    /**
     * @depends test_Getconfig
     */
    public function test_Startrental()
    {
        $this->setupPost("Startrental");

        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["amountpaid"] = $this->package->getCost() * 3;

        $startRental = new Startrental();
        $this->assertSame("Not processed",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("ok",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(0,$startRental->getOutputObject()->getSwapTagInt("owner_payment"),"incorrect owner payment");
        

        $_POST["avatarUUID"] = "499c3e36-69b3-40e5-9229-0cfa5db30766";
        $_POST["avatarName"] = "Test Buyer";
        $_POST["packageuid"] = $this->package->getPackageUid();
        $_POST["amountpaid"] = $this->package->getCost() + 4;

        $startRental = new Startrental();
        $this->assertSame("Not processed",$startRental->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$startRental->getLoadOk(),"Load ok failed");
        $startRental->process();
        $this->assertSame("Payment amount not accepted",$startRental->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(false,$startRental->getOutputObject()->getSwapTagBool("status"),"marked as ok");

        
    }

    protected function setupPost(string $target)
    {
        global $_POST, $slconfig;
        $_POST["method"] = "Buy";
        $_POST["action"] = $target;
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "Madpeter Zond";
        $_POST["pos"] = "123,123,55";
        $_POST["objectname"] = "Testing Object";
        $_POST["objecttype"] = "Test";
        $storage = [
            "method",
            "action",
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
        $raw = time()  . implode("",$real) . $slconfig->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $this->package = new Package();
        $this->package->loadID(1);
        $this->assertSame("UnitTestPackage",$this->package->getName(),"Test package not loaded");
    }
}
