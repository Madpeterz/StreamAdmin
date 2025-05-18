<?php

namespace Tests\Control;

use App\Endpoint\Control\Coupons\Create;
use App\Endpoint\Control\Coupons\Remove;
use App\Endpoint\Control\Coupons\Update;
use App\Models\Marketplacecoupons;
use App\Models\Staff;
use Tests\TestWorker;

class CouponsTest extends TestWorker
{
    public function test_Create()
    {
        global $system;
        $staff = new Staff();
        $staff->setOwnerLevel(true);
        $staff->setAvatarLink(1);
        $system->getSession()->attachStaffMember($staff);
        $Create = new Create();
        $_POST["cost"] = 100;
        $_POST["listingid"] = 54321;
        $_POST["credit"] = 100;
        $Create->process();
        $reply = $Create->getOutputObject();
        $this->assertStringContainsString("Coupon created", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Create
     */
    public function test_Update()
    {
        global $system;
        $coupon = new Marketplacecoupons();
        $coupon->loadByListingid(54321);
        $this->assertSame(true, $coupon->isLoaded(), "Unable to find coupon");
        $staff = new Staff();
        $staff->setOwnerLevel(true);
        $staff->setAvatarLink(1);
        $system->getSession()->attachStaffMember($staff);
        $system->setPage($coupon->getId());
        $_POST["cost"] = 1000;
        $_POST["listingid"] = 12345;
        $_POST["credit"] = 1000;
        $Update = new Update();
        $Update->process();
        $reply = $Update->getOutputObject();
        $this->assertStringContainsString("Coupon updated", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
    /**
     * @depends test_Update
     */
    public function test_Remove()
    {
        global $system;
        $coupon = new Marketplacecoupons();
        $coupon->loadByListingid(12345);
        $this->assertSame(true, $coupon->isLoaded(), "Unable to find coupon");
        $staff = new Staff();
        $staff->setOwnerLevel(true);
        $staff->setAvatarLink(1);
        $system->getSession()->attachStaffMember($staff);
        $system->setPage($coupon->getId());
        $_POST["accept"] = "yes";
        $remove = new Remove();
        $remove->process();
        $reply = $remove->getOutputObject();
        $this->assertStringContainsString("Coupon removed", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
    }
}
