<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Slconfig\Update;
use App\Endpoint\View\Slconfig\DefaultView;
use App\R7\Model\Avatar;
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
        $this->assertStringContainsString("Venders & Servers",$statuscheck,$missing);
        $this->assertStringContainsString("Resellers",$statuscheck,$missing);
        $this->assertStringContainsString("resellers rate (As a %)",$statuscheck,$missing);
        $this->assertStringContainsString("Misc settings",$statuscheck,$missing);
        $this->assertStringContainsString("API default email",$statuscheck,$missing);
        $this->assertStringContainsString("Discord join link",$statuscheck,$missing);
        $this->assertStringContainsString("SL group url",$statuscheck,$missing);
        $this->assertStringContainsString("Renter hud",$statuscheck,$missing);
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
        
        $_POST["newResellersRate"] = 5;
        $_POST["newResellers"] = 1;
        $_POST["event_storage"] = 0;
        $_POST["owneravuid"] = $avatar->getAvatarUid();
        $_POST["ui_tweaks_clients_fulllist"] = 0;
        $_POST["ui_tweaks_datatableItemsPerPage"] = 25;
        $_POST["apiDefaultEmail"] = "unittest@gmail.com";
        $_POST["displayTimezoneLink"] = 1;


        $_POST["hudAllowDiscord"] = 0;
        $_POST["hudDiscordLink"] = "testing";
        $_POST["hudAllowGroup"] = 1;
        $_POST["hudGroupLink"] = "more testing";
        $_POST["hudAllowDetails"] = 0;
        $_POST["hudAllowRenewal"] = 1;

        $updateHandler->process();
        $statuscheck = $updateHandler->getOutputObject();
        $this->assertStringContainsString("system config updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
