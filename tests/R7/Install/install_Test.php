<?php

namespace StreamadminTest;

use App\Db;
use App\Endpoint\View\Install\DefaultView as InstallerDefault;
use App\Endpoint\View\Install\Install;
use App\Endpoint\View\Install\Setup as InstallerStep4;
use App\Models\Avatar;
use App\Models\Slconfig;
use PHPUnit\Framework\TestCase;
use YAPF\MySQLi\MysqliEnabled;

class Installer extends TestCase
{
    protected function setUp(): void
    {
        if(defined("INSTALLMODE") == false) {
            define("INSTALLMODE",true);
        }
        
    }
    public function test_RemoveOldConfig()
    {
        $remove_db_config_status = true;
        if(file_exists("src/App/Config/db_installed.php") == true) {
            $remove_db_config_status = unlink("src/App/Config/db_installed.php");
        }
        $remove_site_installed_status = true;
        if(file_exists("src/App/Config/site_installed.php") == true) {
            $remove_site_installed_status = unlink("src/App/Config/site_installed.php");
        }
        $this->assertSame(true,$remove_db_config_status,"Unable to remove DB installed");
        $this->assertSame(true,$remove_site_installed_status,"Unable to remove site installed");
    }
    public function test_ClearDatabase()
    {
        $sql = new MysqliEnabled();
        $status = $sql->rawSQL("tests/wipeDB.sql");
        $this->assertSame(true,$status["status"]);
    }
    public function test_ShowFormEnterDatabaseDetails()
    {
        $Install = new InstallerDefault();
        $Install->process();
        $statuscheck = $Install->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Continue",$statuscheck);
        $this->assertStringContainsString("ip/domain to the host: Default localhost",$statuscheck);
    }
    public function test_ProcessFormEnterDatabaseDetails()
    {
        global $_POST;
        $Install = new InstallerDefault();
        $db = new Db();
        $_POST["db_host"] = $db->dbHost;
        $_POST["db_name"] = $db->dbName;
        $_POST["db_user"] = $db->dbUser;
        $_POST["db_pass"] = $db->dbPass;
        $Install->process("src/App/Endpoint/View/Install/Required/db.tmp.php","src/App/Config/db_installed.php");
        $statuscheck = $Install->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Test config",$statuscheck);
        $this->assertStringContainsString("DB config ready",$statuscheck);
    }
    public function test_InstallDatabase()
    {
        $Install = new Install();
        $Install->process("src/App/Versions/installer.sql");
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
        $_POST["domain"] = "http://localhost";
        $_POST["sitename"] = "streamadmin test units";
        $_POST["av_username"] = "Madpeter";
        $_POST["av_uuid"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["av_name"] = "MadpeterUnit ZondTest";
        $_POST["av_email"] = "noemailgiven@gmail.com";
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
}
