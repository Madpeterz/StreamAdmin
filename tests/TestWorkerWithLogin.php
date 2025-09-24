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
            $avatarid = $avatar->getId();
        }
        else
        {
            $avatar = new Avatar();
            $avatar->setAvatarUUID("289c3e36-69b3-40c5-9229-0c6a5d230766");
            $avatar->setAvatarName("Default Avatar");
            $avatar->setAvatarUid("testing");
            $avatar->setCredits(0);
            $create = $avatar->createEntry();
            if($create->status == false)
            {
                die("Could not create default avatar for testing");
            }
            $avatarid = $avatar->getId();
        }
        global $system;
        $system->getSlConfig()->setClientsListMode(false);
        $system->getSlConfig()->updateEntry();
        $rental = new Rental();
        $rental->setAvatarLink($avatarid);
        $rental->setStreamLink(1);
        $rental->setStartUnixtime(time());
        $rental->setExpireUnixtime(time() + (60*60*60*24*6));
        $rental->setNoticeLink(1);
        $rental->setPackageLink(1);
        $rental->setRenewals(0);
        $rental->setMessage("Unit test rental");
        $rental->setTotalAmount(405);
        $rental->setRentalUid("unittest");
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
        $stream->setRentalLink($rental->getId());
        $update = $stream->updateEntry();
        if($update->status == false)
        {
            die("Could not update default stream for testing");
        }
        $resellerhelper = new ResellerHelper();
        $status = $resellerhelper->loadOrCreate($avatar->getId(),true,44);
        if($status === false) {
           die("Could not create default reseller for testing");
        }
    }
    
}