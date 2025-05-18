<?php

namespace Tests\Control;

use App\Endpoint\Control\Avatar\Create;
use App\Endpoint\Control\Avatar\Finder;
use App\Endpoint\Control\Avatar\Remove;
use App\Endpoint\Control\Avatar\Update;
use App\Models\Avatar;
use App\Models\Sets\AvatarSet;
use App\Models\Staff;
use Tests\TestWorker;

class AvatarTest extends TestWorker
{
    public function test_Create()
    {
        global $system;
        $avatarCreate = new Create();
        $_POST["avatarName"] = "Unittest";
        $_POST["avatarUUID"] = "281c3e36-69b3-40c5-9229-0c6a5d230766";
        $avatarCreate->process();
        $reply = $avatarCreate->getOutputObject();
        $this->assertSame("Avatar created", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
    }
    public function test_FinderByName()
    {
        $avatarFinder = new Finder();
        $_POST["avatarfind"] = "Madpeter";
        $avatarFinder->process();
        $reply = $avatarFinder->getOutputObject();
        $this->assertSame("ok", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $values = $reply->getSwapTagArray("values");
        $this->assertSame('{"score":76,"matchuid":"SysDevOp","matchname":"Madpeter Zond"}', json_encode($values), "Unexpected values");
        $this->assertSame("SysDevOp", $values["matchuid"], "Expected reply uid for avatar via finder is not correct: " . json_encode($values));
    }
    public function test_FinderByUuid()
    {
        $avatarFinder = new Finder();
        $_POST["avatarfind"] = "a5d230766";
        $avatarFinder->process();
        $reply = $avatarFinder->getOutputObject();
        $this->assertSame("ok", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $values = $reply->getSwapTagArray("values");
        $this->assertSame('{"score":40,"matchuid":"SysDevOp","matchname":"Madpeter Zond"}', json_encode($values), "Unexpected values");
        $this->assertSame("SysDevOp", $values["matchuid"], "Expected reply uid for avatar via finder is not correct: " . json_encode($values));
    }
    public function test_FinderByUid()
    {
        $avatarFinder = new Finder();
        $_POST["avatarfind"] = "SysDe";
        $avatarFinder->process();
        $reply = $avatarFinder->getOutputObject();
        $this->assertSame("ok", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $values = $reply->getSwapTagArray("values");
        $this->assertSame('{"score":11,"matchuid":"SysDevOp","matchname":"Madpeter Zond"}', json_encode($values), "Unexpected values");
        $this->assertSame("SysDevOp", $values["matchuid"], "Expected reply uid for avatar via finder is not correct: " . json_encode($values));
    }

    /**
     * @depends test_Create
     */
    public function test_UpdateName()
    {
        $avatar = new Avatar();
        $load = $avatar->loadByAvatarUUID("281c3e36-69b3-40c5-9229-0c6a5d230766");
        $this->assertSame(true, $load->status, "Unable to find testing avatar");
        global $system;
        $system->setPage($avatar->getAvatarUid());
        $avatarUpdate = new Update();
        $_POST["avatarName"] = "UnittestUpdate Zond";
        $_POST["avatarUUID"] = "281c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["credits"] = 0;
        $avatarUpdate->process();
        $reply = $avatarUpdate->getOutputObject();
        $this->assertSame("Avatar updated", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $avatar = new Avatar();
        $load = $avatar->loadByAvatarUUID("281c3e36-69b3-40c5-9229-0c6a5d230766");
        $this->assertSame("UnittestUpdate Zond", $avatar->getAvatarName(), "Avatar name did not update");
    }
    /**
     * @depends test_UpdateName
     */
    public function test_UpdateCredits()
    {
        $avatar = new Avatar();
        $load = $avatar->loadByAvatarUUID("281c3e36-69b3-40c5-9229-0c6a5d230766");
        $this->assertSame(true, $load->status, "Unable to find testing avatar");
        $this->assertSame(0, $avatar->getCredits(), "Credit level not as expected");
        global $system;
        $system->setPage($avatar->getAvatarUid());
        $staff = new Staff();
        $staff->setOwnerLevel(true);
        $staff->setAvatarLink(1);
        $system->getSession()->attachStaffMember($staff);
        $avatarUpdate = new Update();
        $_POST["avatarName"] = "UnittestUpdate Zond";
        $_POST["avatarUUID"] = "281c3e36-69b3-40c5-9229-0c6a5d230766";
        $_POST["credits"] = 100;
        $avatarUpdate->process();
        $reply = $avatarUpdate->getOutputObject();
        $this->assertSame("Avatar updated", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $avatar = new Avatar();
        $load = $avatar->loadByAvatarUUID("281c3e36-69b3-40c5-9229-0c6a5d230766");
        $this->assertSame(100, $avatar->getCredits(), "Avatar credits not updated as expected");
    }
    /**
     * @depends test_UpdateCredits
     */
    public function test_UpdateUuid()
    {
        $avatar = new Avatar();
        $load = $avatar->loadByAvatarUUID("281c3e36-69b3-40c5-9229-0c6a5d230766");
        $this->assertSame(true, $load->status, "Unable to find testing avatar");
        $this->assertSame(100, $avatar->getCredits(), "Credit level not as expected");
        global $system;
        $system->setPage($avatar->getAvatarUid());
        $avatarUpdate = new Update();
        $_POST["avatarName"] = "UnittestUpdate Zond";
        $_POST["avatarUUID"] = "281c3e36-69b3-40c5-9229-0c6a5d230765";
        $_POST["credits"] = 100;
        $avatarUpdate->process();
        $reply = $avatarUpdate->getOutputObject();
        $this->assertSame("Avatar updated", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $avatar = new Avatar();
        $load = $avatar->loadByAvatarUUID("281c3e36-69b3-40c5-9229-0c6a5d230765");
        $this->assertSame(100, $avatar->getCredits(), "Avatar credits check not as expected");
        $this->assertSame("UnittestUpdate Zond", $avatar->getAvatarName(), "Avatar name check not as expected");
    }

    /**
     * @depends test_UpdateUuid
     */
    public function test_Remove()
    {
        $avatarset = new AvatarSet();
        $this->assertSame(3, $avatarset->countInDB()->items, "Incorrect number of avatars in DB");
        $avatar = new Avatar();
        $load = $avatar->loadByAvatarUUID("281c3e36-69b3-40c5-9229-0c6a5d230765");
        $this->assertSame(true, $load->status, "Unable to find testing avatar");
        $this->assertSame(100, $avatar->getCredits(), "Credit level not as expected");
        global $system;
        $system->setPage($avatar->getAvatarUid());
        $_POST["accept"] = "Accept";
        $avatarRemove = new Remove();
        $avatarRemove->process();
        $reply = $avatarRemove->getOutputObject();
        $this->assertSame("Avatar removed", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");
        $avatarset = new AvatarSet();
        $this->assertSame(2, $avatarset->countInDB()->items, "Incorrect number of avatars in DB");
    }
}
