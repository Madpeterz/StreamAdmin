<?php

namespace StreamAdminR7;

use App\Endpoint\View\Install\DefaultView as InstallerStep1;
use App\Endpoint\View\Install\Finalstep as InstallerStep5;
use App\Endpoint\View\Install\Install as InstallerStep3;
use App\Endpoint\View\Install\Setup as InstallerStep4;
use App\Endpoint\View\Install\Test as InstallerStep2;
use App\R7\Model\Avatar;
use App\R7\Model\Slconfig;
use PHPUnit\Framework\TestCase;

class Installer extends TestCase
{
    protected function setUp(): void
    {
        if(defined("INSTALLMODE") == false) {
            define("INSTALLMODE",true);
        }
    }
    public function test_ShowFormEnterDatabaseDetails()
    {
        $Install = new InstallerStep1();
        $Install->process();
        $statuscheck = $Install->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Continue",$statuscheck);
        $this->assertStringContainsString("ip/domain to the host: Default localhost",$statuscheck);
    }
    public function test_ProcessFormEnterDatabaseDetails()
    {
        global $_POST;
        $Install = new InstallerStep1();
        $_POST["db_host"] = "127.0.0.1";
        $_POST["db_name"] = "test";
        $_POST["db_user"] = "testsuser";
        $_POST["db_pass"] = "testsuserPW";
        $Install->process();
        $statuscheck = $Install->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Test config",$statuscheck);
        $this->assertStringContainsString("DB config ready",$statuscheck);
    }
    public function test_ShowTestDatabaseMessage()
    {
        include "src/App/Config/db.php";
        $Install = new InstallerStep2();
        $Install->process();
        $statuscheck = $Install->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Connected [OK]",$statuscheck);
        $this->assertStringContainsString("Skip install - Goto setup",$statuscheck);
    }
    public function test_ClearDatabase()
    {
        global $sql;
        if($sql->getDatabaseName() != "test") {
            die("Error - Running test_InstallDatabase / test_ClearDatabase via unit test not on the test database!");
        }
        $status = $sql->rawSQL("tests/wipeDB.sql");
        $this->assertSame(true,$status["status"],"Unable to wipe DB: ".$status["message"]);
    }
    public function test_InstallDatabase()
    {
        global $sql;
        if($sql->getDatabaseName() != "test") {
            die("Error - Running test_InstallDatabase / test_ClearDatabase via unit test not on the test database!");
        }
        $Install = new InstallerStep3();
        $Install->process();
        $statuscheck = $Install->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Streamadmin DB installed [OK]",$statuscheck);
    }
    public function test_ShowFormAdjustment()
    {
        $step4 = new InstallerStep4();
        $step4->process();
        $statuscheck = $step4->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Do not use this option unless told to!",$statuscheck);
        $this->assertStringContainsString("Does not have to match SL name",$statuscheck);
        $this->assertStringContainsString("Skip setup goto final",$statuscheck);
    }
    public function test_ProcessFormAdjustment()
    {
        global $_POST;
        $step4 = new InstallerStep4();
        $_POST["domain"] = "http://localhost/";
        $_POST["sitename"] = "streamadmin test units";
        $_POST["av_username"] = "Madpeter";
        $_POST["av_uuid"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["av_name"] = "MadpeterUnit ZondTest";
        $step4->process("src/App/Config/site_installed.php");
        $statuscheck = $step4->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("User config applyed",$statuscheck);
        $this->assertStringContainsString("Final changes",$statuscheck);
        $this->assertSame(true,file_exists("src/App/Config/site_installed.php"),"Site config not saved");
    }
    public function test_VaildateConfigApplyed()
    {
        $av = new Avatar();
        $this->assertSame(true,$av->loadID(1),"Unable to load main avatar");
        $this->assertSame("MadpeterUnit ZondTest",$av->getAvatarName(),"DB not updated with correct test name");
        $siteconfig = new Slconfig();
        $this->assertSame(true,$siteconfig->loadID(1),"Unable to load site config");
    }
    public function test_FinalChangesApplyed()
    {
        $installer = new InstallerStep5();
        $installer->process();
        $statuscheck = $installer->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("docker please set: INSTALL_OK to 1",$statuscheck);
        $this->assertStringContainsString("Goto login",$statuscheck);
        $siteconfig = new Slconfig();
        $this->assertSame(true,$siteconfig->loadID(1),"Unable to load site config");
        $this->assertStringContainsString("SL link code: ".$siteconfig->getSlLinkCode(),$statuscheck);
        $this->assertSame(true,file_exists("src/App/Config/ready.txt"),"ready flag not saved");
    }
}
