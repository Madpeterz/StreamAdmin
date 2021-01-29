<?php

namespace StreamadminTest;

use App\Endpoint\Control\Objects\Clear as ObjectsClear;
use App\Endpoint\View\Objects\Clear;
use App\Endpoint\View\Objects\DefaultView;
use PHPUnit\Framework\TestCase;

class ObjectsTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $missing = "Missing Objects element";
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Owner",$statuscheck,$missing);
        $this->assertStringContainsString("Last seen",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_ClearForm()
    {
        $clearform = new Clear();
        $clearform->process();
        $missing = "Missing Objects clearform element";
        $statuscheck = $clearform->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Accept",$statuscheck,$missing);
        $this->assertStringContainsString("Clear all objects (DB only)",$statuscheck,$missing);
    }

    public function test_ClearProcess()
    {
        global $_POST;
        $_POST["accept"] = "Accept";
        $clearprocess = new ObjectsClear();
        $clearprocess->process();
        $statuscheck = $clearprocess->getOutputObject();
        $this->assertStringContainsString("Objects cleared from DB",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}