<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Client\NoticeOptout;
use App\Endpoint\SecondLifeApi\Noticeserver\Next;
use App\Endpoint\View\Client\Manage;
use App\Models\Rental;
use App\Models\Rentalnoticeptout;
use App\Models\Sets\MessageSet;
use PHPUnit\Framework\TestCase;

class Issue92 extends TestCase
{
    public function test_CheckCurrentMessageQ()
    {
        $MessageSet = new MessageSet();
        $this->assertSame(7,$MessageSet->countInDB(),"Incorrect number of messages in the Q");
    }

    /**
     * @depends test_CheckCurrentMessageQ
     */
    public function test_AdjustClient()
    {
        global $system;
        $rental = new Rental();
        $this->assertSame(true,$rental->loadid(11)->status,"Unable to load rental");
        $rental->setNoticeLink(10);
        $rental->setExpireUnixtime(time()+($system->unixtimeDay()*7)-$system->unixtimeHour());
        $this->assertSame(true,$rental->updateEntry()->status,"Failed to update rental");
    }

    /**
     * @depends test_AdjustClient
     */
    public function test_AddOptOut()
    {
        $optOut = new Rentalnoticeptout();
        $optOut->setRentalLink(11);
        $optOut->setNoticeLink(1);
        $this->assertSame(true,$optOut->createEntry()->status,"Failed to create opt out");
    }

    /**
     * @depends test_AddOptOut
     */
    public function test_ProcessNoticeChange()
    {
        $Next = new Next();
        $this->assertSame("ready",$Next->getOutputObject()->getSwapTagString("message"),"Ready checks failed");
        $this->assertSame(true,$Next->getLoadOk(),"Load ok failed");
        $Next->process();
        $this->assertSame("ok",$Next->getOutputObject()->getSwapTagString("message"),"incorrect reply");
        $this->assertSame(true,$Next->getOutputObject()->getSwapTagBool("status"),"marked as failed");

        $rental = new Rental();
        $this->assertSame(true,$rental->loadid(9),"Unable to load rental");
        $this->assertSame(1,$rental->getNoticeLink(),"Rental has incorrect notice level");
    }

    /**
     * @depends test_ProcessNoticeChange
     */
    public function test_ReCheckCurrentMessageQ()
    {
        $MessageSet = new MessageSet();
        $this->assertSame(7,$MessageSet->countInDB(),"Incorrect number of messages in the Q");
    }

    /**
     * @depends test_ReCheckCurrentMessageQ
     */
    public function test_UIshowsDisable()
    {
        global $system;

        $rental = new Rental();
        $this->assertSame(true,$rental->loadid(9)->status,"Unable to load rental");
        $this->assertSame(1,$rental->getNoticeLink(),"Rental has incorrect notice level");
        $system->setPage( $rental->getRentalUid());
        $manageForm  = new Manage();
        $manageForm->process();
        $statuscheck = $manageForm->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing client manage element";
        $this->assertStringContainsString("Notice opt-out",$statuscheck,$missing);
        $this->assertStringContainsString("remove-optout-1",$statuscheck,$missing);
        $this->assertStringContainsString("add-optout-6",$statuscheck,$missing);
    }

    /**
     * @depends test_UIshowsDisable
     */
    public function test_UIupdateOptout()
    {
        global $_POST, $system;

        $rental = new Rental();
        $this->assertSame(true,$rental->loadid(9)->status,"Unable to load rental");
        $this->assertSame(1,$rental->getNoticeLink(),"Rental has incorrect notice level");
        $system->setPage( $rental->getRentalUid());

        $_POST["remove-optout-1"] = 1;
        $_POST["add-optout-6"] = 1;
        $updateOptout = new NoticeOptout();
        $updateOptout->process();
        $statuscheck = $updateOptout->getOutputObject();
        $this->assertSame("Opt-outs updated enabled: 1 and removed 1",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

}

