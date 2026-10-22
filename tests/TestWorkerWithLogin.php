<?php

namespace Tests;
use App\Framework\SessionControl;
use App\Helpers\ResellerHelper;
use App\Models\Avatar;
use App\Models\Rental;
use App\Models\Staff;
use App\Models\Stream;
use Tests\Control\PackageTest;
use Tests\Control\ServerTest;
use Tests\Control\StreamTest;

class TestWorkerWithLogin extends TestWorker
{
    public static function setUpBeforeClass(): void
    {
        global $system;
        parent::setUpBeforeClass();
        $session = new SessionControlTesting();
        $staff = new Staff();
        $load = $staff->loadId(1);
        if (!$load->status) {
            throw new \Exception("Unable to load staff user for testing");
        }
        $session->attachStaffMember($staff);
        $system->attachSession($session);
                $packages = new PackageTest();
        $packages->test_Create();

        $servers = new ServerTest();
        $servers->test_Create();

        $streams = new StreamTest();
        $streams->test_Create();

        $avatar = new Avatar();
        $loadorcreate = $avatar->loadByAvatarUUID("289c3e36-69b3-40c5-9229-0c6a5d230766");
        $avatarid = -1;
        if($loadorcreate->status == true) {
            $avatarid = $avatar->_Id;
        }
        else
        {
            $avatar = new Avatar();
            $avatar->_AvatarUUID = "289c3e36-69b3-40c5-9229-0c6a5d230766";
            $avatar->_AvatarName = "Default Avatar";
            $avatar->_AvatarUid = "testing";
            $avatar->_Credits = 0;
            $create = $avatar->createEntry();
            if($create->status == false)
            {
                die("Could not create default avatar for testing");
            }
            $avatarid = $avatar->_Id;
        }
        global $system;
        $system->getSlConfig()->_ClientsListMode = false;
        $system->getSlConfig()->updateEntry();
        $rental = new Rental();
        $rental->_AvatarLink = $avatarid;
        $rental->_StreamLink = 1;
        $rental->_StartUnixtime = time();
        $rental->_ExpireUnixtime = time() + (60*60*60*24*6);
        $rental->_NoticeLink = 1;
        $rental->_PackageLink = 1;
        $rental->_Renewals = 0;
        $rental->_Message = "Unit test rental";
        $rental->_TotalAmount = 405;
        $rental->_RentalUid = "unittest";
        $create = $rental->createEntry();
        if($create->status == false)
        {
            die("Could not create default rental for testing");
        }
        $stream = new Stream();
        $load = $stream->loadId(1);
        if($load->status == false)
        {
            die("Could not load default stream for testing");
        }
        $stream->_RentalLink = $rental->_Id;
        $update = $stream->updateEntry();
        if($update->status == false)
        {
            die("Could not update default stream for testing");
        }
        $resellerhelper = new ResellerHelper();
        $status = $resellerhelper->loadOrCreate($avatar->_Id,true,44);
        if($status === false) {
           die("Could not create default reseller for testing");
        }
    }
    
}