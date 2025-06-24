<?php

namespace Tests\Secondlife;

use App\Endpoint\Control\Package\Create AS CreatePackage;
use App\Endpoint\Control\Server\Create AS CreateServer;
use App\Endpoint\Control\Tree\Addpackage;
use App\Endpoint\Control\Tree\Create;
use App\Endpoint\Secondlifeapi\Tree\Getpackages;
use App\Models\Set\PackageSet;
use App\Models\Set\TreevenderpackagesSet;
use Tests\TestWorker;

class TreeEndpointTest extends TestWorker
{
    public function test_PreCreate()
    {
        // server
        $_POST["domain"] = "test.mypanel.com";
        $_POST["controlPanelURL"] = "https://test.mypanel.com/client";
        $_POST["ipaddress"] = "1.1.1.1";
        $create = new CreateServer();
        $create->process();
        $reply = $create->getOutputObject();
        $this->assertSame("Server created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");

        // package
        $loop = 0;
        while($loop < 5)
        {
            $this->resetPost();
            $_POST["name"] = "unittest".$loop;
            $_POST["templateLink"] = 1;
            $_POST["cost"] = 1234;
            $_POST["days"] = 55+$loop;
            $_POST["bitrate"] = 125;
            $_POST["listeners"] = 25+$loop;
            $_POST["textureSoldout"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
            $_POST["textureInstockSmall"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
            $_POST["textureInstockSelected"] = "51d5f381-43cd-84f0-c226-f9f89c12af7e";
            $_POST["enableGroupInvite"] = false;
            $_POST["autodj"] = false;
            $_POST["autodjSize"] = 0;
            $_POST["servertypeLink"] = 1;
            $_POST["welcomeNotecardLink"] = 1;
            $_POST["setupNotecardLink"] = 1;
            $create = new CreatePackage();
            $create->process();
            $reply = $create->getOutputObject();
            $this->assertSame("Package created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
            $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
            $loop++;
        }
    }
    /**
     * @depends test_PreCreate
     */
    public function test_Create()
    {
        $this->resetPost();
        $_POST["name"] = "tree test";
        $create = new Create();
        $create->process();
        $reply = $create->getOutputObject();
        $this->assertSame("Tree vender created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Create
     */
    public function test_AddPackage()
    {
        $this->resetPost();
        global $system;
        $system->setPage(1);

        $packages = new PackageSet();
        $packages->loadAll();
        $this->assertSame(5, $packages->getCount(), "Incorrect number of packages found");

        foreach($packages as $package)
        {
            $_POST["package"] = $package->getId();
            $addPackage = new Addpackage();
            $addPackage->process();
            $reply = $addPackage->getOutputObject();
            $this->assertSame("Package added to tree vender", $reply->getSwapTagString("message"), "Message does not appear to be correct");
            $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        }

        $treeVenderPackages = new TreevenderpackagesSet();
        $treeVenderPackages->loadByTreevenderLink(1);
        $this->assertSame(5, $treeVenderPackages->getCount(), "Incorrect number of packages linked");
    }
    /**
     * @depends test_Create
     */
    public function test_GetPackages()
    {
        global $system;
        $system->setModule("Tree");
        $system->setArea("Getpackages");
        $this->slAPI();
        $_POST["tree_vender_id"] = 1;
        $tree = new Getpackages();
        $status = $tree->getOutputObject()->addSwapTagString("message");
        $this->assertSame("ready", $status, "tree should have 'ready' status before processing");
        $tree->process();
        $reply = $tree->getOutputObject();
        $this->assertSame("ok", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $replyCosts = $reply->getSwapTagArray("package_cost");
        $this->assertSame(5, count($replyCosts), "Incorrect number of costs returned");
    }

}
