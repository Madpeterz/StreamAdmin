<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Client\Api;
use App\Endpoint\Control\Client\Bulkremove as ClientBulkremove;
use App\Endpoint\View\Client\BulkRemove;
use App\R7\Set\DetailSet;
use App\R7\Set\RentalSet;
use PHPUnit\Framework\TestCase;

class FinalsClient extends TestCase
{
    public function test_ClientApi()
    {
        global $page, $optional;
        
        $rentals = new RentalSet();
        $rentals->loadNewest(1);
        $page = $rentals->getFirst()->getRentalUid();
        $optional = "Stop";
        $Api = new Api();
        $Api->process();
        $statuscheck = $Api->getOutputObject();
        $this->assertStringContainsString(
            "API/Ok => server stopped",
            $statuscheck->getSwapTagString("message"),
            "incorrect reply"
        );
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_ClientApi
     */
    public function test_ClientBulkRemoveView()
    {
        global $unixtime_day;
        $clientSet = new RentalSet();
        $status = $clientSet->loadAll();
        $this->assertSame(true,$status["status"],"Unable to load rentals");
        $fields = ["expireUnixtime","noticeLink"];
        $values = [time()-($unixtime_day*3),6];
        $status = $clientSet->updateMultipleFieldsForCollection($fields,$values);
        $this->assertSame(true,$status["status"],"Unable to update rentals to expired");

        $bulkRemoveView = new BulkRemove();
        $bulkRemoveView->process();
        $statuscheck = $bulkRemoveView->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing bulk remove element";
        $this->assertStringContainsString("Unlisted clients",$statuscheck,$missing);
        $this->assertStringContainsString("Message on account",$statuscheck,$missing);
        $this->assertStringContainsString("5151",$statuscheck,$missing);
        $this->assertStringContainsString("Test Buyer",$statuscheck,$missing);
        $this->assertStringContainsString("Remove",$statuscheck,$missing);
        $this->assertStringContainsString("Expired",$statuscheck,$missing);
        $this->assertStringContainsString("Skip",$statuscheck,$missing);
        $this->assertStringContainsString("NoticeLevel",$statuscheck,$missing);
        $this->assertStringContainsString("Process",$statuscheck,$missing);
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
                if(strlen($rental->getMessage()) > 0)
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
            "Bad reply: Api Azurecast does not support: getEventRecreateRevoke",
            $statuscheck->getSwapTagString("message"),
            "incorrect reply"
        );
        $this->assertSame(false,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}