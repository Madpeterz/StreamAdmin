<?php

declare(strict_types=1);

namespace Login;

use App\Models\Avatar;
use App\Models\Package;
use App\Models\Staff;
use Tests\Control\PackageTest;
use Tests\Control\ServerTest;
use Tests\Control\StreamTest;

require "bootstrap.php";

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
        die("[Login] Could not create default avatar");
    }
}

$staff = new Staff();
$load = $staff->loadId(1);
if($load->status == false)
{
    die("[Login] Could not load staff id 1");
}
$staff->setUsername("testing");
$system->getSession()->attachStaffMember($staff);
$reply = $system->getSession()->createStaffPasswordHash("testing");
$staff->setPhash($reply->phash);
$staff->setPsalt($reply->psalt);
$staff->setOwnerLevel(true);
$staff->setAvatarLink($avatarid);
$reply = $staff->updateEntry();
if($reply->status == false)
{
    die("[Login] Could not setup login");
}
echo "[Login] Ready to login as testing / testing\n";