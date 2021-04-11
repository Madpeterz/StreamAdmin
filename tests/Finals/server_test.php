<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Server\Getapiconfig;
use App\Endpoint\Control\Server\ServerLoad;
use App\Endpoint\Control\Server\SyncAccounts;
use PHPUnit\Framework\TestCase;

class FinalsServer extends TestCase
{
    public function test_ServerLoad()
    {
        global $page;
        $page = 1;
        $ServerLoad = new ServerLoad();
        $ServerLoad->process();
        $statuscheck = $ServerLoad->getOutputObject();
        $this->assertStringContainsString("<span class=\"text-",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Unexpected status");
    }

    public function test_ServerSync()
    {
        global $page;
        $page = 1;
        $serverSync = new SyncAccounts();
        $serverSync->process();
        $statuscheck = $serverSync->getOutputObject();
        $this->assertStringContainsString("Updated: 0 / Ok: 0 / Account missing: 10",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Unexpected status");
    }

    public function test_ServerApiConfig()
    {
        global $_POST;
        $_POST["apiLink"] = 2;
        $serverSync = new Getapiconfig();
        $serverSync->process();
        $statuscheck = $serverSync->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("update_api_flags"));
        $this->assertSame("API config loaded",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Unexpected status");
    }
}