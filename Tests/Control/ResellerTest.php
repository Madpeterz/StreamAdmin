<?php

namespace Tests\Control;

use App\Endpoint\Control\Reseller\Remove;
use App\Endpoint\Control\Reseller\Update;
use App\Models\Reseller;
use App\Models\Sets\ResellerSet;
use Tests\TestWorker;

class ResellerTest extends TestWorker
{
    public function test_RequiredFirst()
    {
        $reseller = new Reseller();
        $reseller->setAllowed(false);
        $reseller->setAvatarLink(1);
        $reseller->setRate(100);
        $create = $reseller->createEntry();
        $this->assertSame("ok", $create->message, "Failed to create reseller 1");
        $this->assertSame(true, $create->status, "Failed to create reseller 1");
        $reseller = new Reseller();
        $reseller->setAllowed(false);
        $reseller->setAvatarLink(2);
        $reseller->setRate(100);
        $create = $reseller->createEntry();
        $this->assertSame("ok", $create->message, "Failed to create reseller 2");
        $this->assertSame(true, $create->status, "Failed to create reseller 2");
    }
    /**
     * @depends test_RequiredFirst
     */
    public function test_Update()
    {
        global $system;
        $_POST["rate"] = 50;
        $_POST["allowed"] = true;
        $system->setPage(2);
        $resellerPreUpdate = new Reseller();
        $resellerPreUpdate->loadId(2);
        $this->assertSame(100, $resellerPreUpdate->getRate(), "Reseller rate is not correct pre update");
        $this->assertSame(false, $resellerPreUpdate->getAllowed(), "Reseller allowed is not correct pre update");
        $update = new Update();
        $update->process();
        $reply = $update->getOutputObject();
        $this->assertSame("Reseller updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $resellerPostUpdate = new Reseller();
        $resellerPostUpdate->loadId(2);
        $this->assertSame(50, $resellerPostUpdate->getRate(), "Reseller rate is not correct post update");
        $this->assertSame(true, $resellerPostUpdate->getAllowed(), "Reseller allowed is not correct post update");
    }
    /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        global $system;
        $resellers = new ResellerSet();
        $this->assertSame(2,$resellers->countInDB(null)->items,"Incorrect number of resellers in DB pre remove");
        $_POST["accept"] = "Accept";
        $system->setPage(2);
        $remove = new Remove();
        $remove->process();
        $reply = $remove->getOutputObject();
        $this->assertSame("Reseller removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $this->assertSame(1,$resellers->countInDB(null)->items,"Incorrect number of resellers in DB post remove");
    }

}
