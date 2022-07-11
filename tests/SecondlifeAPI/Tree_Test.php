<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Package\Create;
use App\Endpoint\SecondLifeApi\Tree\Getpackages;
use App\Models\Package;
use App\Models\Treevender;
use App\Models\Treevenderpackages;
use PHPUnit\Framework\TestCase;

class SecondlifeApiTree extends TestCase
{
    public function test_RecreateTreeVender()
    {
        $PackageCreateHandler = new Create();
        $_POST["name"] = "AlsoUnitTestPackage";
        $_POST["templateLink"] = 1;
        $_POST["cost"] = 66;
        $_POST["days"] = 5;
        $_POST["bitrate"] = 56;
        $_POST["listeners"] = 10;
        $_POST["textureSoldout"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSmall"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSelected"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["autodj"] = false;
        $_POST["autodjSize"] = 0;
        $_POST["apiTemplate"] = "None";
        $_POST["servertypeLink"] = 1;
        $_POST["welcomeNotecardLink"] = 1;
        $_POST["setupNotecardLink"] = 1;
        $PackageCreateHandler->process();
        $statuscheck = $PackageCreateHandler->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertStringContainsString("Package created",$statuscheck->getSwapTagString("message"));
        $package = new Package();
        $this->assertSame(true,$package->loadByName("AlsoUnitTestPackage"),"Load package failed");
        $treevender = new Treevender();
        $treevender->setName("Demo");
        $this->assertSame(true,$treevender->createEntry()->status,"create tree vender failed");
        $treevender = new Treevender();
        $this->assertSame(true,$treevender->loadByName("Demo"),"Load ok failed");
        $treepackage = new Treevenderpackages();
        $treepackage->setTreevenderLink($treevender->getId());
        $treepackage->setPackageLink($package->getId());
        $status = $treepackage->createEntry();
        $this->assertSame("ok",$status["message"],"create first tree vender package failed");
        $this->assertSame(true,$status->status,"create first tree vender package failed");
        $treepackage = new Treevenderpackages();
        $treepackage->setTreevenderLink($treevender->getId());
        $treepackage->setPackageLink(1);
        $status = $treepackage->createEntry();
        $this->assertSame("ok",$status["message"],"create first tree vender package failed");
        $this->assertSame(true,$status->status,"create first tree vender package failed");
    }
    public function test_Getpackages()
    {
        global $_POST, $system;
        $_POST["method"] = "Tree";
        $_POST["action"] = "Getpackages";
        $_POST["mode"] = "test";
        $_POST["objectuuid"] = "b36971ef-b2a5-f461-025c-81bbc473deb8";
        $_POST["regionname"] = "Testing";
        $_POST["ownerkey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["ownername"] = "MadpeterUnit ZondTest";
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
        $raw = time()  . implode("",$real) . $system->getSlConfig()->getSlLinkCode();
        $_POST["hash"] = sha1($raw);
        $treevender = new Treevender();
        $this->assertSame(true,$treevender->loadByName("Demo"),"Load ok failed");
        $_POST["tree_vender_id"] = $treevender->getId();
        $Notecardsync = new Getpackages();
        $this->assertSame("ready",$Notecardsync->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Notecardsync->getLoadOk(),"Load ok failed");
        $Notecardsync->process();
        $this->assertSame("ok",$Notecardsync->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Notecardsync->getOutputObject()->getSwapTagBool("status"),"marked as failed");
        $this->assertSame(2,count($Notecardsync->getOutputObject()->getSwapTagArray("packageUid")),"incorrect number of treevender packages loaded");
    }
}
