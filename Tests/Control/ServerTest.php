<?php

namespace Tests\Control;

use App\Endpoint\Control\Server\Create;
use App\Endpoint\Control\Server\Remove;
use App\Endpoint\Control\Server\Update;
use App\Models\Server;
use App\Models\Sets\ServerSet;
use Tests\TestWorker;

class ServerTest extends TestWorker
{
    public function test_Create()
    {
        $_POST["domain"] = "test.mypanel.com";
        $_POST["controlPanelURL"] = "https://test.mypanel.com/client";
        $_POST["ipaddress"] = "1.1.1.1";
        $create = new Create();
        $create->process();
        $reply = $create->getOutputObject();
        $this->assertSame("Server created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }

    /**
     * @depends test_Create
     */
    public function test_Update()
    {
        global $system;
        $_POST["domain"] = "test.mypanel.com";
        $_POST["controlPanelURL"] = "https://test.mypanel.com/client";
        $_POST["ipaddress"] = "2.1.2.1";
        $system->setPage(1);
        $serverPreUpdate = new Server();
        $serverPreUpdate->loadId(1);
        $this->assertSame("1.1.1.1", $serverPreUpdate->getIpaddress(), "server ip is not correct pre update");
        $update = new Update();
        $update->process();
        $reply = $update->getOutputObject();
        $this->assertSame("Server updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $serverPostUpdate = new Server();
        $serverPostUpdate->loadId(1);
        $this->assertSame("2.1.2.1", $serverPostUpdate->getIpaddress(), "server ip is not correct post update");
    }
    /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        global $system;
        $servers = new ServerSet();
        $this->assertSame(1,$servers->countInDB(null)->items,"Incorrect number of servers in DB pre remove");
        $serverPostUpdate = new Server();
        $serverPostUpdate->loadId(1);
        $this->assertSame(1,$serverPostUpdate->getId(),"Unable to find server 1");
        $_POST["accept"] = "Accept";
        $system->setPage(1);
        $remove = new Remove();
        $remove->process();
        $reply = $remove->getOutputObject();
        $this->assertSame("Server removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $this->assertSame(0,$servers->countInDB(null)->items,"Incorrect number of servers in DB post remove");
    }

}
