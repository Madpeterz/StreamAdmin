<?php

namespace Tests\Control;

use App\Endpoint\Control\Slconfig\Paymentkeyupdate;
use App\Endpoint\Control\Slconfig\Reissue;
use App\Endpoint\Control\Slconfig\Update;
use App\Endpoint\View\Slconfig\Paymentkey;
use Tests\SessionControlTesting;
use Tests\TestWorker;

class SlconfigTest extends TestWorker
{
    public function test_Paymentkeyupdate()
    {
        global $system;
        $expiretime = time() + $system->unixtimeWeek() + $system->unixtimeHour(); 
        $webSign = sha1("testings" . $system->getSiteURL() . $expiretime . "web");
        $webSign = substr($webSign, 0, 3);
        $testKey = "testings:".$expiretime."*".$webSign;
        $keyCheck = new Paymentkey();
        $result = $keyCheck->getKeyStatus($testKey,true);
        $this->assertStringContainsString("7 days",$result->message,"Expected time reply not correct");
        $this->assertSame(true,$result->status,"Key check failed pre update");
        $_POST["assignedkey"] = $testKey;
        $Paymentkeyupdate = new Paymentkeyupdate();
        $Paymentkeyupdate->process();
        $reply = $Paymentkeyupdate->getOutputObject();
        $this->assertSame("Key updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }

    public function test_Reissue()
    {
        global $system;
        $system->attachSession(new SessionControlTesting());
        $Reissue = new Reissue();
        $Reissue->process();
        $reply = $Reissue->getOutputObject();
        $this->assertSame("keys reissued!", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code"); 
    }

    public function test_Update()
    {
        $_POST["newResellersRate"] = 40;
        $_POST["newResellers"] = 0;
        $_POST["owneravuid"] = "System";
        $_POST["ui_tweaks_clientsShowServer"] = 0;
        $_POST["ui_tweaks_groupStreamsBy"] = 0;
        $_POST["ui_tweaks_clients_fulllist"] = 1;
        $_POST["ui_tweaks_datatableItemsPerPage"] = 35;
        $_POST["displayTimezoneLink"] = 3;
        $_POST["eventsAPI"] = 1;
        $_POST["enableCoupons"] = 1;
        $_POST["ansSalt"] = "23142124123";
        $_POST["limitStreams"] = 1;
        $_POST["limitTime"] = 0;
        $_POST["maxStreamTimeDays"] = 7;
        $_POST["maxTotalStreams"] = 1;
        $_POST["hudAllowDiscord"] = 0;
        $_POST["hudDiscordLink"] = "https://google.com";
        $_POST["hudAllowGroup"] = 1;
        $_POST["hudGroupLink"] = "group join link";
        $_POST["hudAllowDetails"] = 1;
        $_POST["hudAllowRenewal"] = 0;
        $update = new Update();
        $update->process();
        $reply = $update->getOutputObject();
        $this->assertSame("System config updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}
