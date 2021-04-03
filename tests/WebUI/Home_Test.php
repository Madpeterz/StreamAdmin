<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Home\Cleanup;
use PHPUnit\Framework\TestCase;

class HomeTest extends TestCase
{
    public function test_Default()
    {
        $default = new Cleanup();
        $default->process();
        $missing = "Missing Cleanup element";
        $statuscheck = $default->getOutputObject();
        $this->assertStringContainsString("Unable to run cleanup code (it would delete unit tests...)",$statuscheck->getSwapTagString("message"),$missing);
    }


}