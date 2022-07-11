<?php 

declare(strict_types=1);

namespace Tests;

use App\Config;

define("UNITTEST", "yep");

include "./vendor/autoload.php";
include "test.db.php";
global $system;
$system = new Config();
$system->setFolders("src", "");

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (headers_sent() == false) {
        session_start();
    }
}