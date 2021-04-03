<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Login\Reset;
use App\Endpoint\Control\Login\Resetnow;
use App\Endpoint\Control\Login\Start as LoginWithPassword;
use App\Endpoint\View\Login\DefaultView as LoginPage;
use App\Endpoint\View\Login\Logout;
use App\Endpoint\View\Login\Reset as LoginReset;
use App\Endpoint\View\Login\Resetwithtoken;
use App\R7\Set\MessageSet;
use App\R7\Model\Staff;
use PHPUnit\Framework\TestCase;

class Login extends TestCase
{
    public function test_ShowLoginPage()
    {
        $LoginPage = new LoginPage();
        $LoginPage->process();
        $statuscheck = $LoginPage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Login",$statuscheck);
        $this->assertStringContainsString("Who are you?",$statuscheck);
        $this->assertStringContainsString("Whats your password?",$statuscheck);
        $this->assertStringContainsString("Hi there please login",$statuscheck);
    }

    public function test_ShowResetPasswordPage()
    {
        global $area;
        $area = "reset";
        $LoginPage = new LoginReset();
        $LoginPage->process();
        $statuscheck = $LoginPage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("SL username",$statuscheck);
        $this->assertStringContainsString("Oh snap you forgot your details",$statuscheck);
    }

    public function test_ProcessResetPassword()
    {
        global $_POST;
        $resetHandeler = new Reset();
        $_POST["slusername"] = "MadpeterUnit ZondTest";
        $resetHandeler->process();
        $statuscheck = $resetHandeler->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertStringContainsString("reset code is on the way",$statuscheck->getSwapTagString("message"));
    }

    public function test_ShowResetWithToken()
    {
        global $area;
        $messages = new MessageSet();
        $messages->loadAll();
        $this->assertSame(2,$messages->getCount(),"Status check failed");
        $area = "resetwithtoken";
        $LoginPage = new Resetwithtoken();
        $LoginPage->process();
        $statuscheck = $LoginPage->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Reset token",$statuscheck);
        $this->assertStringContainsString("Update",$statuscheck);
    }

    public function test_ProcessResetWithToken()
    {
        global $_POST;
        $staff = new Staff();
        $this->assertSame(true,$staff->loadID(1),"Unable to load staff account");
        $Resetnow = new Resetnow();
        $_POST["slusername"] = "MadpeterUnit ZondTest";
        $_POST["newpassword1"] = "asdasdasd1";
        $_POST["newpassword2"] = "asdasdasd1";
        $_POST["token"] = $staff->getEmailResetCode();
        $Resetnow->process();
        $statuscheck = $Resetnow->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertStringContainsString("Password updated please login",$statuscheck->getSwapTagString("message"));
    }

    public function test_LoginWithPassword()
    {
        global $_POST;
        $LoginWithPassword = new LoginWithPassword();
        $_POST["staffusername"] = "Madpeter";
        $_POST["staffpassword"] = "asdasdasd1";
        $LoginWithPassword->process();
        $statuscheck = $LoginWithPassword->getOutputObject();
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $this->assertStringContainsString("logged in ^+^",$statuscheck->getSwapTagString("message"));
    }

    public function test_IsLoggedIn()
    {
        global $session;
        $session->loadFromSession();
        $this->assertSame(true,$session->getLoggedIn(),"Not logged in");
        $this->assertSame(true,$session->getOwnerLevel(),"Not logged in as system owner");
    }

    public function test_Logout()
    {
        global $session;
        $session->loadFromSession();
        $this->assertSame(true,$session->getLoggedIn(),"Not logged in [Required for logout]");
        $logout = new Logout();
        $logout->process();
        $statuscheck = $logout->getOutputObject()->getSwapTagString("page_content");
        $this->assertStringContainsString("Logged out",$statuscheck);
        $this->assertSame(false,$session->getLoggedIn(),"Still logged in even thou we logged out");
    }

    public function test_LogoutReloadLogin()
    {
        global $session;
        $this->assertSame(false,$session->loadFromSession(),"Still logged in even thou we killed the session");
        $this->assertSame(false,$session->getLoggedIn(),"Still logged in even thou we killed the session");
        $this->test_LoginWithPassword();
        $this->assertSame(true,$session->loadFromSession(),"Failed to restore session");
        $this->assertSame(true,$session->getLoggedIn(),"failed to login again");
    }


}
