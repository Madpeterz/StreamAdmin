<?php

namespace StreamAdminR7;

use PHPUnit\Framework\TestCase;

class UrlLoadingTest extends TestCase
{
    public function test_Module()
    {
        global $testsystem;
        $testsystem->forceProcessURI("testing");
        $this->assertSame("Testing",$testsystem->getModule());
    }

    public function test_ModuleAndArea()
    {
        global $testsystem;
        $testsystem->forceProcessURI("access/testing");
        $this->assertSame("Access",$testsystem->getModule());
        $this->assertSame("Testing",$testsystem->getArea());
    }

    public function test_ModuleAreaAndPage()
    {
        global $testsystem;
        $testsystem->forceProcessURI("url/access/testing");
        $this->assertSame("Url",$testsystem->getModule());
        $this->assertSame("Access",$testsystem->getArea());
        $this->assertSame("Testing",$testsystem->getPage());
    }

    public function test_ModuleAreaPageAndOptional()
    {
        global $testsystem;
        $testsystem->forceProcessURI("dynamic/url/access/testing");
        $this->assertSame("Dynamic",$testsystem->getModule());
        $this->assertSame("Url",$testsystem->getArea());
        $this->assertSame("Access",$testsystem->getPage());
        $this->assertSame("Testing",$testsystem->getOption());
    }
}
