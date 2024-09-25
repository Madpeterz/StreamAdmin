<?php

namespace StreamAdminR7;

use App\Endpoint\View\Config\DefaultView;
use Tests\Mytest;

class ConfigTest extends Mytest
{
    public function test_ConfigMenu()
    {
        $config = new DefaultView();
        $config->process();
        $statuscheck = $config->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing config page element";
        $this->assertStringContainsString("Avatars",$statuscheck,$missing);
        $this->assertStringContainsString("Notices",$statuscheck,$missing);
        $this->assertStringContainsString("Banlist",$statuscheck,$missing);
    }
}