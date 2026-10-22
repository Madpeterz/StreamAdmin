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
$staff->_Username = "testing";
$system->getSession()->attachStaffMember($staff);
$reply = $system->getSession()->createStaffPasswordHash("testing");
$staff->_Phash = $reply->phash;
$staff->_Psalt = $reply->psalt;
$staff->_OwnerLevel = true;
$staff->_AvatarLink = 2;
$reply = $staff->updateEntry();
if($reply->status == false)
{
    die("[Login] Could not setup login");
}
echo "[Login] Ready to login as testing / testing\n";