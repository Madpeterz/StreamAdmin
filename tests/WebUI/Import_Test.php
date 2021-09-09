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

    /**
     * @depends test_Default
     */
    public function test_Setup()
    {
        $Setup = new Setup();
        $Setup->process();
        $missing = "Missing Objects clearform element";
        $statuscheck = $Setup->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("db_host",$statuscheck,$missing);
        $this->assertStringContainsString("db_pass",$statuscheck,$missing);
        $this->assertStringContainsString("Setup",$statuscheck,$missing);
    }

    /**
     * @depends test_Setup
     */
    public function test_Setconfig()
    {
        global $_POST;
        $_POST["db_host"] = "127.0.0.1";
        $_POST["db_name"] = "r4test";
        $_POST["db_username"] = "testsuser";
        $_POST["db_pass"] = "testsuserPW";
        $Setconfig = new Setconfig();
        $Setconfig->process();
        $statuscheck = $Setconfig->getOutputObject();
        $this->assertSame("Ok",$statuscheck->getSwapTagString("message"),"Incorrect reply");
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertSame(true,file_exists("" . ROOTFOLDER . "/App/Config/r4.php"),"Did not create R4 config file!");
    }

    /**
     * @depends test_Setconfig
     */
    public function test_Servers()
    {
        $Servers = new Servers();
        $Servers->process();
        $statuscheck = $Servers->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 servers",$statuscheck,$missing);
    }


    /**
     * @depends test_Servers
     */
    public function test_Packages()
    {
        $Packages = new Packages();
        $Packages->process();
        $statuscheck = $Packages->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 packages",$statuscheck,$missing);
    }

    /**
     * @depends test_Packages
     */
    public function test_Avatars()
    {
        $Avatars = new Avatars();
        $Avatars->process();
        $statuscheck = $Avatars->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 avatars",$statuscheck,$missing);
    }

    /**
     * @depends test_Avatars
     */
    public function test_Streams()
    {
        $Streams = new Streams();
        $Streams->process();
        $statuscheck = $Streams->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 streams",$statuscheck,$missing);
    }

    /**
     * @depends test_Streams
     */
    public function test_Clients()
    {
        $Clients = new Clients();
        $Clients->process();
        $statuscheck = $Clients->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 clients",$statuscheck,$missing);
    }

    /**
     * @depends test_Clients
     */
    public function test_Transactions()
    {
        $Clients = new Transactions();
        $Clients->process();
        $statuscheck = $Clients->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing Objects element";
        $this->assertStringContainsString("Created: 1 transactions",$statuscheck,$missing);
    }
}