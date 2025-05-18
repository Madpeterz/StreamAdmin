<?php

namespace Tests\Admin\Control;

use App\Endpoint\Control\Template\Create;
use App\Endpoint\Control\Template\Remove;
use App\Endpoint\Control\Template\Update;
use Tests\TestWorker;

class TemplateTest extends TestWorker
{
    public function test_Create()
    {
        $_POST["name"] = "unittest";
        $_POST["detail"] = "This is a detail";
        $_POST["notecardDetail"] = "This is a notecard detail";
        $create = new Create();
        $create->process();
        $reply = $create->getOutputObject();
        $this->assertSame("Template created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Create
     */
    public function test_Update()
    {
        $_POST["name"] = "unittesting";
        $_POST["detail"] = "This is a detail a";
        $_POST["notecardDetail"] = "This is a notecard detail b";
        global $system;
        $system->setPage(3);
        $Update = new Update();
        $Update->process();
        $reply = $Update->getOutputObject();
        $this->assertSame("Template updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        global $system;
        $_POST["accept"] = "Accept";
        $system->setPage(3);
        $Update = new Remove();
        $Update->process();
        $reply = $Update->getOutputObject();
        $this->assertSame("Template removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}

