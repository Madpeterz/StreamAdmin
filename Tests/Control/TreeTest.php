<?php

namespace Tests\Control;

use App\Endpoint\Control\Package\Create AS CreatePackage;
use App\Endpoint\Control\Server\Create AS CreateServer;
use App\Endpoint\Control\Tree\Addpackage;
use App\Endpoint\Control\Tree\Create;
use App\Endpoint\Control\Tree\Remove;
use App\Endpoint\Control\Tree\Removepackage;
use App\Endpoint\Control\Tree\Update;
use App\Models\Set\PackageSet;
use App\Models\Set\TreevenderpackagesSet;
use Tests\TestWorker;

class TreeTest extends TestWorker
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
     * @depends test_AddPackage
     */
    public function test_RemovePackage()
    {
        $this->resetPost();
        global $system;

        $treeVenderPackages = new TreevenderpackagesSet();
        $treeVenderPackages->loadByTreevenderLink(1);
        $this->assertSame(5, $treeVenderPackages->getCount(), "Incorrect number of packages linked");
        $system->setPage($treeVenderPackages->getFirst()->getId());

        $_POST["accept"] = "Accept";

        $removePackage = new Removepackage();
        $removePackage->process();
        $reply = $removePackage->getOutputObject();
        $this->assertSame("Tree vender linked package removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        
        $treeVenderPackages = new TreevenderpackagesSet();
        $treeVenderPackages->loadByTreevenderLink(1);
        $this->assertSame(4, $treeVenderPackages->getCount(), "Incorrect number of packages linked");
    }
    /**
     * @depends test_RemovePackage
     */
    public function test_Update()
    {
        global $system;
        $this->resetPost();
        $_POST["name"] = "tree test update";
        $_POST["textureWaiting"] = "728fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["textureInuse"] = "738fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["hideSoldout"] = "748fdaf8-df99-5c7f-48fb-feb94db12675";
        $system->setPage(1);
        $Update = new Update();
        $Update->process();
        $reply = $Update->getOutputObject();
        $this->assertSame("Treevender updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        global $system;
        $this->resetPost();
        $_POST["accept"] = "Accept";
        $system->setPage(1);


        $Remove = new Remove();
        $Remove->process();
        $reply = $Remove->getOutputObject();
        $this->assertSame("Tree vender removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        
        $treeVenderPackages = new TreevenderpackagesSet();
        $treeVenderPackages->loadAll();
        $this->assertSame(0, $treeVenderPackages->getCount(), "Incorrect number of packages linked remaining");
    }
}