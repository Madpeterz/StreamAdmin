<?php

namespace Tests\Admin\Control;

use App\Endpoint\Control\Package\Create;
use App\Models\Package;
use Tests\TestWorker;

class PackageTest extends TestWorker
{
    public function test_Create()
    {
        $_POST["name"] = "unittest";
        $_POST["templateLink"] = 1;
        $_POST["cost"] = 1234;
        $_POST["days"] = 55;
        $_POST["bitrate"] = 125;
        $_POST["listeners"] = 25;
        $_POST["textureSoldout"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $_POST["textureInstockSmall"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $_POST["textureInstockSelected"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
        $_POST["enableGroupInvite"] = false;
        $_POST["autodj"] = false;
        $_POST["autodjSize"] = 0;
        $_POST["servertypeLink"] = 1;
        $_POST["welcomeNotecardLink"] = 1;
        $_POST["setupNotecardLink"] = 1;
        $clear = new Create();
        $clear->process();
        $reply = $clear->getOutputObject();
        $this->assertSame("Package created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Create
     */
    public function test_Update()
    {
        $package = new Package();
        $package->loadByName("unittest");
        $_POST["enforceCustomMaxStreams"] = true;
        $_POST["maxStreamsInPackage"] = 10;
    }
}
