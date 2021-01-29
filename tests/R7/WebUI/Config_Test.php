<?php

namespace StreamAdminR7;

use App\Endpoint\View\Config\DefaultView;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
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