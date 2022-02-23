<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Slconfig\Update;
use App\Models\Avatar;
use PHPUnit\Framework\TestCase;

class Issue57 extends TestCase
{
    public function test_addGroupInviteToMessageQ()
    {       
        global $_POST;
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
        $_POST["hudDiscordLink"] = "";
        $_POST["hudAllowGroup"] = 1;
        $_POST["hudGroupLink"] = "";
        $_POST["hudAllowDetails"] = 0;
        $_POST["hudAllowRenewal"] = 1;
        $_POST["eventsAPI"] = 1;

        $updateHandler->process();
        $statuscheck = $updateHandler->getOutputObject();
        $this->assertSame("System config updated",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }
}
