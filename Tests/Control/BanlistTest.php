<?php

namespace Tests\Control;

use App\Endpoint\Control\Banlist\Clear;
use App\Endpoint\Control\Banlist\Create;
use Tests\TestWorker;

class BanlistTest extends TestWorker
{
    public function test_Create()
    {
        $banlistCreate = new Create();
        $_POST["uid"] = "System";
        $banlistCreate->process();
        $reply = $banlistCreate->getOutputObject();
        $this->assertSame("Entry created", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(1, $reply->getSwapTagInt("newbanid"), "incorrect banlist id entry");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
    /**
     * @depends test_Create
     */
    public function test_Remove()
    {
        global $system;
        $system->setPage(1);
        $banlistCreate = new Clear();
        $banlistCreate->process();
        $reply = $banlistCreate->getOutputObject();
        $this->assertSame("Entry removed", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
}
