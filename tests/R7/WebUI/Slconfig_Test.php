<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Slconfig\Update;
use App\Endpoint\View\Slconfig\DefaultView;
use App\Models\Avatar;
use PHPUnit\Framework\TestCase;

class Slconfigtest extends TestCase
{
    public function test_Default()
    {
        $ServersList = new DefaultView();
        $ServersList->process();
        $statuscheck = $ServersList->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing slconfig form element";
        $this->assertStringContainsString("Core",$statuscheck,$missing);
        $this->assertStringContainsString("Current owner: MadpeterUnit ZondTest",$statuscheck,$missing);
        $this->assertStringContainsString("Link code [SL->Server]",$statuscheck,$missing);
        $this->assertStringContainsString("SMTP [Email sending support]",$statuscheck,$missing);
        $this->assertStringContainsString("Host",$statuscheck,$missing);
        $this->assertStringContainsString("Resellers",$statuscheck,$missing);
        $this->assertStringContainsString("resellers rate (As a %)",$statuscheck,$missing);
        $this->assertStringContainsString("Misc settings",$statuscheck,$missing);
        $this->assertStringContainsString("API default email",$statuscheck,$missing);
        $this->assertStringContainsString("Update",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_Manageprocess()
    {
        global $_POST, $slconfig;
        $updateHandler = new Update();
        $avatar = new Avatar();
        $status = $avatar->loadID($updateHandler->getSlConfigObject()->getOwnerAvatarLink());
        $this->assertSame(true,$status,"Unable to load system owner avatar");
        
        $_POST["slLinkCode"] = "1345tfgred";
        $_POST["httpcode"] = "asdas231a3241";
        $_POST["publicLinkCode"] = "4gfj6fd3frty";
        $_POST["newResellersRate"] = 5;
        $_POST["newResellers"] = 1;
        $_POST["event_storage"] = 0;
        $_POST["owneravuid"] = $avatar->getAvatarUid();
        $_POST["ui_tweaks_clients_fulllist"] = 0;
        $_POST["ui_tweaks_datatableItemsPerPage"] = 25;
        $_POST["apiDefaultEmail"] = "unittest@gmail.com";
        $_POST["displayTimezoneLink"] = 1;
        $_POST["smtpFrom"] = "unittest@gmail.com";
        $_POST["smtp_reply"] = "unittest@gmail.com";
        $_POST["smtpHost"] = "smtp.gmail.com";
        $_POST["smtp_user"] = "unittest";
        $_POST["smtp_code"] = "unittest";
        $_POST["smtpPort"] = 54;
        $updateHandler->process();
        $statuscheck = $updateHandler->getOutputObject();
        $this->assertStringContainsString("system config updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
