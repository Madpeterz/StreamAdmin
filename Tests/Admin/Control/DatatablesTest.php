<?php

namespace Tests\Admin\Control;

use App\Endpoint\Control\Datatables\Update;
use Tests\TestWorker;

class DatatablesTest extends TestWorker
{
    public function test_Update()
    {
        global $system;
        $system->setPage(1);
        $_POST["col"] = 2;
        $_POST["dir"] = "asc";
        $Update = new Update();
        $Update->process();
        $reply = $Update->getOutputObject();
        $this->assertStringContainsString("Datatable config updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}
