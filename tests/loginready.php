<?php

declare(strict_types=1);

namespace Login;

use App\Models\Staff;
use Tests\TestWorkerWithLogin;
use Tests\View\ClientTest;

require "bootstrap.php";

$ClientTest = new TestWorkerWithLogin();
$ClientTest->setUpBeforeClass();

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
$staff->setAvatarLink(2);
$reply = $staff->updateEntry();
if($reply->status == false)
{
    die("[Login] Could not setup login");
}
echo "[Login] Ready to login as testing / testing\n";