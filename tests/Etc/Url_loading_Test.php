<?php

namespace StreamAdminR7;

use PHPUnit\Framework\TestCase;

class UrlLoadingTest extends TestCase
{
    public function test_Module()
    {
        global $system;
        $system->forceProcessURI("testing");
        $this->assertSame("Testing",$system->getModule());
    }

    public function test_ModuleAndArea()
    {
        global $system;
        $system->forceProcessURI("access/testing");
        $this->assertSame("Access",$system->getModule());
        $this->assertSame("Testing",$system->getArea());
    }

    public function test_ModuleAreaAndPage()
    {
        global $system;
        $system->forceProcessURI("url/access/testing");
        $this->assertSame("Url",$system->getModule());
        $this->assertSame("Access",$system->getArea());
        $this->assertSame("Testing",$system->getPage());
    }

    public function test_ModuleAreaPageAndOptional()
    {
        global $system;
        $system->forceProcessURI("dynamic/url/access/testing");
        $this->assertSame("Dynamic",$system->getModule());
        $this->assertSame("Url",$system->getArea());
        $this->assertSame("Access",$system->getPage());
        $this->assertSame("Testing",$system->getOption());
    }
}
