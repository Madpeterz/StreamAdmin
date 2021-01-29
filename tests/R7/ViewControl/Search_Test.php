<?php

namespace StreamadminTest;

use App\Endpoint\View\Search\DefaultView;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    public function test_Default()
    {
        global $_GET;
        $default = new DefaultView();
        $_GET["search"] = "Mad";
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing search result element";
        $this->assertStringContainsString("Clients [1]",$statuscheck,$missing);
        $this->assertStringContainsString("Avatars [1]",$statuscheck,$missing);
        $this->assertStringContainsString("Streams [1]",$statuscheck,$missing);
        $this->assertStringContainsString("MadpeterUnit ZondTest",$statuscheck,$missing);
        $this->assertStringContainsString("Active",$statuscheck,$missing);
        $this->assertStringContainsString("8002",$statuscheck,$missing);
        $this->assertStringContainsString("Sold -> MadpeterUnit ZondTest",$statuscheck,$missing);
        $this->assertStringContainsString("Testing",$statuscheck,$missing);
    }
}