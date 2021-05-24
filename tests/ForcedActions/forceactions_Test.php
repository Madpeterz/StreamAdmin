<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Client\Create as CreateCleintHandler;
use App\Endpoint\Control\Package\Create as PackageCreateHandler;
use App\Endpoint\Control\Server\Create as serverCreateHandler;
use App\Endpoint\Control\Stream\Create as streamCreateHandler;
use App\Endpoint\View\Client\Create as ClientCreateForm;
use App\Endpoint\View\Client\DefaultView as clientListByNoticeLevel;
use App\Endpoint\View\Package\Create as PackageCreateForm;
use App\Endpoint\View\Home\DefaultView as Dashboard;
use App\Endpoint\View\Package\DefaultView as PackagesList;
use App\Endpoint\View\Server\Create as ServerCreateForm;
use App\Endpoint\View\Server\DefaultView as ServersList;
use App\Endpoint\View\Stream\Create as StreamCreateForm;
use App\Endpoint\View\Stream\DefaultView as StreamPackageSelect;
use PHPUnit\Framework\TestCase;

class ForcedActions extends TestCase
{
    public function setUp(): void
    {
        include ROOTFOLDER . "/App/Framework/load.php";  
    }
    public function test_ShowDashboard()
    {
        if (defined("INSTALLED") == false) {
            $this->assertSame(true,false,"INSTALLED flag is not set!");
        }
        $dashboard = new Dashboard();
        $dashboard->process();
        $statuscheck = $dashboard->getOutputObject()->getSwapTagString("page_content");
        $missing_dashboard = "Missing dashboard element";
        $this->assertStringContainsString("SL health",$statuscheck,$missing_dashboard);
        $this->assertStringContainsString("servers",$statuscheck,$missing_dashboard);
        $this->assertStringContainsString("Clients",$statuscheck,$missing_dashboard);
        $this->assertStringContainsString("Streams",$statuscheck,$missing_dashboard);
        $this->assertStringContainsString("Version",$statuscheck,$missing_dashboard);

        $missing_menu = "Missing menu element";
        $statuscheck = $dashboard->getOutputObject()->getSwapTagString("html_menu");
        $this->assertStringContainsString("Dashboard",$statuscheck,$missing_menu);
        $this->assertStringContainsString("Streams",$statuscheck,$missing_menu);
        $this->assertStringContainsString("Config",$statuscheck,$missing_menu);
    }

    public function test_ShowPackages()
    {
        $PackagesList = new PackagesList();
        $PackagesList->process();
        $statuscheck = $PackagesList->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing package list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("Days",$statuscheck,$missing);
        $this->assertStringContainsString("Kbps",$statuscheck,$missing);
        $this->assertStringContainsString("Listeners",$statuscheck,$missing);
    }

    public function test_ShowPackagesCreateForm()
    {
        $PackageCreateForm = new PackageCreateForm();
        $PackageCreateForm->process();
        $statuscheck = $PackageCreateForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing package create form element";
        $this->assertStringContainsString("Terms",$statuscheck,$missing);
        $this->assertStringContainsString("Basics",$statuscheck,$missing);
        $this->assertStringContainsString("Textures",$statuscheck,$missing);
        $this->assertStringContainsString("Auto DJ",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    public function test_CreatePackage()
    {
        global $_POST;
        $PackageCreateHandler = new PackageCreateHandler();
        $_POST["name"] = "UnitTestPackage";
        $_POST["templateLink"] = 1;
        $_POST["cost"] = 50;
        $_POST["days"] = 7;
        $_POST["bitrate"] = 128;
        $_POST["listeners"] = 50;
        $_POST["textureSoldout"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSmall"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["textureInstockSelected"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["autodj"] = true;
        $_POST["autodjSize"] = 120;
        $_POST["apiTemplate"] = "None";
        $_POST["servertypeLink"] = 1;
        $PackageCreateHandler->process();
        $statuscheck = $PackageCreateHandler->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertStringContainsString("Package created",$statuscheck->getSwapTagString("message"));
    }

    public function test_ShowServers()
    {
        $ServersList = new ServersList();
        $ServersList->process();
        $statuscheck = $ServersList->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing servers list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("Domain",$statuscheck,$missing);
    }

    public function test_ShowServersCreateForm()
    {
        $ServerCreateForm = new ServerCreateForm();
        $ServerCreateForm->process();
        $statuscheck = $ServerCreateForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing server create form element";
        $this->assertStringContainsString("Basic config",$statuscheck,$missing);
        $this->assertStringContainsString("Domain",$statuscheck,$missing);
        $this->assertStringContainsString("API / Password",$statuscheck,$missing);
        $this->assertStringContainsString("Opt / Toggle status",$statuscheck,$missing);
        $this->assertStringContainsString("Event / Update stream on server",$statuscheck,$missing);
        $this->assertStringContainsString("Api notes",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    public function test_CreateServer()
    {
        global $_POST;
        $serverCreateHandler = new serverCreateHandler();
        $_POST["domain"] = "Testing";
        $_POST["controlPanelURL"] = "http://notused.com";
        $_POST["apiLink"] = 1;
        $_POST["apiURL"] = "";
        $_POST["apiUsername"] = "";
        $_POST["apiPassword"] = "";
        $_POST["optPasswordReset"] = 0;
        $_POST["optAutodjNext"] = 0;
        $_POST["optToggleAutodj"] = 0;
        $_POST["eventEnableStart"] = 0;
        $_POST["eventDisableExpire"] = 0;
        $_POST["eventDisableRevoke"] = 0;
        $_POST["eventResetPasswordRevoke"] = 0;
        $_POST["eventEnableRenew"] = 0;
        $_POST["optToggleStatus"] = 0;
        $_POST["eventStartSyncUsername"] = 0;
        $_POST["apiServerStatus"] = 0;
        $_POST["eventClearDjs"] = 0;
        $_POST["eventRevokeResetUsername"] = 0;
        $_POST["eventRecreateRevoke"] = 0;
        $_POST["apiSyncAccounts"] = 0;
        $_POST["eventCreateStream"] = 0;
        $_POST["eventUpdateStream"] = 0;
        $serverCreateHandler->process();
        $statuscheck = $serverCreateHandler->getOutputObject();
        $this->assertStringContainsString("Server created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    public function test_ShowStreamsPackageSelect()
    {
        $StreamPackageSelect = new StreamPackageSelect();
        $StreamPackageSelect->process();
        $statuscheck = $StreamPackageSelect->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing streams packages list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("Name",$statuscheck,$missing);
        $this->assertStringContainsString("Sold",$statuscheck,$missing);
        $this->assertStringContainsString("Ready",$statuscheck,$missing);
    }

    public function test_ShowStreamsCreateForm()
    {
        $StreamCreateForm = new StreamCreateForm();
        $StreamCreateForm->process();
        $statuscheck = $StreamCreateForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing stream create form element";
        $this->assertStringContainsString("Basics",$statuscheck,$missing);
        $this->assertStringContainsString("Config",$statuscheck,$missing);
        $this->assertStringContainsString("API",$statuscheck,$missing);
        $this->assertStringContainsString("Magic",$statuscheck,$missing);
        $this->assertStringContainsString("API UID 1,2 and 3",$statuscheck,$missing);
        $this->assertStringContainsString("Note:",$statuscheck,$missing);
    }

    public function test_CreateStream()
    {
        global $_POST;
        $streamCreateHandler = new streamCreateHandler();
        $_POST["port"] = 8002;
        $_POST["packageLink"] = 1;
        $_POST["serverLink"] = 1;
        $_POST["mountpoint"] = "/live";
        $_POST["adminUsername"] = "UnitTesting";
        $_POST["adminPassword"] = substr(md5(microtime()."a"),0,8);
        $_POST["djPassword"] = substr(md5(microtime()."b"),0,8);
        $_POST["needswork"] = 0;
        $_POST["apiConfigValue1"] = "";
        $_POST["apiConfigValue2"] = "";
        $_POST["apiConfigValue3"] = "";
        $_POST["api_create"] = 0;
        $streamCreateHandler->process();
        $statuscheck = $streamCreateHandler->getOutputObject();
        $this->assertStringContainsString("Stream created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    public function test_clientListByNoticeLevel()
    {
        $clientListByNoticeLevel = new clientListByNoticeLevel();
        $clientListByNoticeLevel->process();
        $statuscheck = $clientListByNoticeLevel->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing clients list element";
        $this->assertStringContainsString("<table",$statuscheck,$missing);
        $this->assertStringContainsString("NoticeLevel",$statuscheck,$missing);
        $this->assertStringContainsString("Count",$statuscheck,$missing);
    }

    public function test_ShowClientCreateForm()
    {
        $ClientCreateForm = new ClientCreateForm();
        $ClientCreateForm->process();
        $statuscheck = $ClientCreateForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client create element";
        $this->assertStringContainsString("Basics",$statuscheck,$missing);
        $this->assertStringContainsString("Find/Add avatar",$statuscheck,$missing);
        $this->assertStringContainsString("you must use the UID!",$statuscheck,$missing);
        $this->assertStringContainsString("Create",$statuscheck,$missing);
    }

    public function test_CreateClient()
    {
        global $_POST;
        $CreateCleintHandler = new CreateCleintHandler();
        $_POST["avataruid"] = "MadpeterUnit ZondTest";
        $_POST["streamuid"] = 8002;
        $_POST["daysremaining"] = 7;
        $CreateCleintHandler->process();
        $statuscheck = $CreateCleintHandler->getOutputObject();
        $this->assertStringContainsString("Client created",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

}
