<?php

namespace Tests\Secondlife;

use App\Endpoint\Secondlifeapi\Texturepack\Getpack;
use Tests\TestWorker;

class TexturepackEndpointTest extends TestWorker
{
    public function test_Getpack()
    {
        global $system;
        $system->setModule("Texturepack");
        $system->setArea("Getpack");
        $this->slAPI();
        $pack = new Getpack();
        $pack->process();
        $reply = $pack->getOutputObject();
        $this->assertTrue(method_exists($reply, 'getSwapTagString'), 'Output object should have getSwapTagString method');
        $this->assertSame("ok", $reply->getSwapTagString("message"), "Expected message not found");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status should be true");
        $this->assertSame("718fdaf8-df99-5c7f-48fb-feb94db12675", $reply->getSwapTagString("Texture-Offline"), "Expected Texture-Offline tag not found");
    }

}
