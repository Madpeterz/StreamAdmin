<?php

namespace Tests\Control;

use App\Endpoint\Control\Textureconfig\Create;
use App\Endpoint\Control\Textureconfig\Remove;
use App\Endpoint\Control\Textureconfig\Update;
use Tests\TestWorker;

class TextureConfigTest extends TestWorker
{
    public function test_Create()
    {
        $_POST["name"] = "unit testing";
        $_POST["gettingDetails"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["requestDetails"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["offline"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["waitOwner"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["inUse"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["makePayment"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["stockLevels"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["renewHere"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $_POST["proxyRenew"] = "718fdaf8-df99-5c7f-48fb-feb94db12675";
        $create = new Create();
        $create->process();
        $reply = $create->getOutputObject();
        $this->assertSame("Texture pack created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Create
     */
    public function test_Update()
    {
        $_POST["name"] = "unit testing update";
        global $system;
        $system->setPage(2);
        $Update = new Update();
        $Update->process();
        $reply = $Update->getOutputObject();
        $this->assertSame("Texture pack updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        global $system;
        $_POST["accept"] = "Accept";
        $system->setPage(2);
        $Update = new Remove();
        $Update->process();
        $reply = $Update->getOutputObject();
        $this->assertSame("Texture pack removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}

