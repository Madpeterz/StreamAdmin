<?php

namespace Tests\View;

use App\Endpoint\View\Search\DefaultView;
use PHPUnit\Framework\TestCase;
use Tests\TestWorker;
use Tests\TestWorkerWithLogin;

class SearchTest extends TestWorkerWithLogin
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
        $this->assertStringContainsString("Madpeter Zond",$statuscheck,$missing);
        $this->assertStringContainsString("Active",$statuscheck,$missing);
        $this->assertStringContainsString("8000",$statuscheck,$missing);
        $this->assertStringContainsString("Sold -> Madpeter Zond",$statuscheck,$missing);
        $this->assertStringContainsString("unittest",$statuscheck,$missing);
    }
}