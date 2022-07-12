<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Client\Bulkremove as ClientBulkremove;
use App\Endpoint\View\Client\BulkRemove;
use App\Models\Rental;
use App\Models\Sets\DetailSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;
use App\Models\Stream;
use PHPUnit\Framework\TestCase;

class FinalsClient extends TestCase
{
    public function test_CreateFakeRentalsToRemove()
    {
        $rentalSet = new RentalSet();
        $rentalSet->loadAll();
        $rentalSet->updateFieldInCollection("noticeLink", 10);

        $portmap = [];
        $port = 7000;
        $streamSet = new StreamSet();
        $allOk = true;
        while($port < 7010)
        {
            $stream = new Stream();
            $stream->setPort($port);
            $stream->setAdminUsername("user".$port);
            $stream->setAdminPassword("user".$port."passwd");
            $stream->setDjPassword("dj".$port."passwd");
            $stream->setStreamUid("port".$port);
            $stream->setServerLink(1);
            $stream->setPackageLink(1);
            $stream->setNeedWork(false);
            $stream->setMountpoint("/live");
            $reply = $stream->createEntry();
            if($reply->status == false)
            {
                $allOk = false;
                break;
            }
            $streamSet->addToCollected($stream);
            $portmap[$port] = $reply->newId;
            $port+=2;
        }
        $this->assertSame(true, $allOk, "Failed to create new streams");

        $port = 7000;
        while($port < 7010)
        {
            $streamId = $portmap[$port];
            $rental = new Rental();
            $rental->setAvatarLink(11);
            $rental->setStreamLink($streamId);
            $rental->setPackageLink(1);
            $rental->setNoticeLink(6);
            $rental->setStartUnixtime(time()-1000);
            $rental->setExpireUnixtime(time()-500);
            $rental->setRentalUid("rent".$port);
            if($port == 7008)
            {
                $rental->setMessage("Dont delete me");
            }
            $reply = $rental->createEntry();
            if($reply->status == false)
            {
                $allOk = false;
                break;
            }
            $stream = $streamSet->getObjectByID($streamId);
            $stream->setRentalLink($reply->newId);
            $reply = $stream->updateEntry();
            if($reply->status == false)
            {
                $allOk = false;
                break;
            }
            $port+=2;
        }
        $this->assertSame(true, $allOk, "Failed to create new rentals");
    }

    /**
     * @depends test_CreateFakeRentalsToRemove
     */
    public function test_ClientBulkRemoveView()
    {
        global $system;
        $bulkRemoveView = new BulkRemove();
        $bulkRemoveView->process();
        $statuscheck = $bulkRemoveView->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing bulk remove element";

        $this->assertStringContainsString('form-control" id="message',$statuscheck,$missing);
        $this->assertStringContainsString("Process",$statuscheck,$missing);
        $this->assertStringContainsString("Skip",$statuscheck,$missing);
        $this->assertStringContainsString("Remove",$statuscheck,$missing);
        $this->assertStringContainsString("7004",$statuscheck,$missing);
        $this->assertStringContainsString("Test Buyer",$statuscheck,$missing);
    }

    /**
     * @depends test_ClientBulkRemoveView
     */
    public function test_ClientBulkRemoveControl()
    {
        global $_POST;
        $detailSet = new DetailSet();
        $detailSet->loadAll();
        $detailSet->purgeCollection();
        
        $rentalSet = new RentalSet();
        $rentalSet->loadAll();
        foreach($rentalSet->getAllIds() as $rentalid)
        {
            $rental = $rentalSet->getObjectByID($rentalid);
            if($rental->getMessage() != null)
            {
                if(nullSafeStrLen($rental->getMessage()) > 0)
                {
                    continue;
                }
            }
            $_POST["rental" . $rental->getRentalUid() . ""] = "purge";
        }
        $bulkRemoveControl = new ClientBulkremove();
        $bulkRemoveControl->process();
        $statuscheck = $bulkRemoveControl->getOutputObject();
        $this->assertSame(
            "Removed 4 rentals! and skipped 1",
            $statuscheck->getSwapTagString("message"),
            "incorrect reply"
        );
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}