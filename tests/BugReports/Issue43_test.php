<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Stream\Update;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Set\PackageSet;
use App\R7\Set\RentalSet;
use App\R7\Set\StreamSet;
use PHPUnit\Framework\TestCase;

class Issue43 extends TestCase
{
    protected $package = null;
    public function test_updateRentalPackageLink()
    {        
        global $sql, $page, $_POST;
        $packages = new PackageSet();
        $packages->loadNewest();
        $targetPackage = $packages->getFirst();

        $package = new Package();
        $package->setPackageUid("is43");
        $package->setApiTemplate("is43");
        $package->setName("is43");
        $package->setTextureInstockSelected("289c3e36-69b3-40c5-9229-0c6a5d230766");
        $package->setTextureInstockSmall("289c3e36-69b3-40c5-9229-0c6a5d230766");
        $package->setTextureSoldout("289c3e36-69b3-40c5-9229-0c6a5d230766");
        $package->setBitrate(128);
        $package->setCost(123);
        $package->setDays(7);
        $package->createEntry();

        $streams = new StreamSet();
        $streams->loadNewest(1);
        $stream = $streams->getFirst();
        $stream->setPackageLink($package->getId());
        $stream->updateEntry();

        $rentals = new RentalSet();
        $rentals->loadNewest(1);
        $rental = $rentals->getFirst();
        $rental->setPackageLink($package->getId());
        $rental->updateEntry();

        $sql->sqlSave();

        $manageProcess = new Update();
        $page = $stream->getStreamUid();
        $_POST["port"] = $stream->getPort();
        $_POST["packageLink"] = $targetPackage->getId(); // package to move to.
        $_POST["serverLink"] = $stream->getServerLink();
        $_POST["mountpoint"] = $stream->getMountpoint();
        $_POST["originalAdminUsername"] = $stream->getAdminUsername();
        $_POST["adminUsername"] = $stream->getAdminUsername();
        $_POST["adminPassword"] = $stream->getAdminPassword();
        $_POST["djPassword"] = $stream->getDjPassword();
        $_POST["needswork"] = $stream->getNeedWork();
        $_POST["apiConfigValue1"] = $stream->getApiConfigValue1();
        $_POST["apiConfigValue2"] = $stream->getApiConfigValue2();
        $_POST["apiConfigValue3"] = $stream->getApiConfigValue3();
        $_POST["api_create"] = 0;
        $manageProcess->process();
        $statuscheck = $manageProcess->getOutputObject();
        $this->assertStringContainsString("Stream updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");

        $sql->sqlSave();

        $rental_test = new Rental();
        $rental_test->loadID($rental->getId());
        $this->assertSame($targetPackage->getId(),$rental_test->getPackageLink(),"Package link did not update as expected");
    }
}
