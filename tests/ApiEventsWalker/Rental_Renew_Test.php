<?php

namespace StreamAdminR7;

use App\R7\Set\ApirequestsSet;
use PHPUnit\Framework\TestCase;

class RentalRenew_Test extends TestCase
{
    public function test_UI_Renew()
    {
    }

    /**
     * @depends test_UI_Renew
    */
    public function test_FirstActionInQ()
    {
        $apiRequests = new ApirequestsSet();
        $this->assertSame(true,$apiRequests->loadAll()["status"],"Status check failed");
        $this->assertSame(1,$apiRequests->getCount(),"Incorrect number of requests in the Q");
    }

    /**
     * @depends test_FirstActionInQ
    */
    public function test_ActionLoops()
    {
    }
}
