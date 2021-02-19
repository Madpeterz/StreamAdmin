<?php

namespace StreamAdminR7;

use PHPUnit\Framework\TestCase;

class UrlLoadingTest extends TestCase
{
    public function test_Module()
    {
        global $module;
        process_uri("testing");
        $this->assertSame("testing",$module);
    }

    public function test_ModuleAndArea()
    {
        global $module, $area;
        process_uri("access/testing");
        $this->assertSame("access",$module);
        $this->assertSame("testing",$area);
    }

    public function test_ModuleAreaAndPage()
    {
        global $module, $area, $page, $optional;
        process_uri("url/access/testing");
        $this->assertSame("url",$module);
        $this->assertSame("access",$area);
        $this->assertSame("testing",$page);
    }

    public function test_ModuleAreaPageAndOptional()
    {
        global $module, $area, $page, $optional;
        process_uri("dynamic/url/access/testing");
        $this->assertSame("dynamic",$module);
        $this->assertSame("url",$area);
        $this->assertSame("access",$page);
        $this->assertSame("testing",$optional);
    }
}
