<?php

namespace Tests\Secondlife;

use App\Endpoint\Secondlifeapi\Object\Ping;
use App\Endpoint\Secondlifeapi\Object\Severrequestcode;
use Tests\TestWorker;

class ObjectEndpointTest extends TestWorker
{
    public function test_Ping()
    {
        $ping = new Ping();
        $ping->process();
        $reply = $ping->getOutputObject();
        $this->assertSame('pong', $reply->getSwapTagString('message'), 'Ping should return pong');
        $this->assertSame(true, $reply->getSwapTagBool('status'), 'Ping should return status true');
   }
    public function test_ServerRequestCode()
    {
        $requestcode = new Severrequestcode();
        $requestcode->setOwnerOverride(true);
        $requestcode->process();
        $reply = $requestcode->getOutputObject();
        $this->assertNotEmpty($reply->getSwapTagString('message'), 'Server request code message should not be empty');
        $this->assertSame(true, $reply->getSwapTagBool('status'), 'Server request code should return status true');
    }
}
