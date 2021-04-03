<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Import\Setconfig;
use App\Endpoint\View\Import\Avatars;
use App\Endpoint\View\Import\Clients;
use App\Endpoint\View\Import\DefaultView;
use App\Endpoint\View\Import\Packages;
use App\Endpoint\View\Import\Servers;
use App\Endpoint\View\Import\Setup;
use App\Endpoint\View\Import\Streams;
use App\Endpoint\View\Import\Transactions;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    public function test_Default()
    {
        $default = new DefaultView();
        $default->process();
        $missing = "Missing Objects element";
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Servers",$statuscheck,$missing);
        $this->assertStringContainsString("Transactions",$statuscheck,$missing);
        $this->assertStringContainsString("import/clients",$statuscheck,$missing);
    }
}