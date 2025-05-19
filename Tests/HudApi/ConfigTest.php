<?php

namespace Tests\HudApi;

use App\Endpoint\Hudapi\Config\Hudconfig;
use Tests\TestWorker;

class ConfigTest extends TestWorker
{
    public function test_Hudconfig()
    {
        $this->makeSLconnection(
            "Config","Hudconfig",
            "289c3e36-69b3-40c5-9229-0c6a5d230767","James Pond",
            "289c3e36-69b3-40c5-9229-0c6a5d230765","Example",
            "Unittest land","Hud");
        $Hudconfig = new Hudconfig();
        $Hudconfig->process();
        $reply = $Hudconfig->getOutputObject();
        $this->assertSame("Get config [ok]", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
}
