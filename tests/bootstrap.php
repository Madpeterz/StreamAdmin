<?php 

declare(strict_types=1);

namespace Tests;

use App\Config;

define("UNITTEST", "yep");

require "./vendor/autoload.php";
require "test.db.php";
require "./src/App/Framework/Functions.php";
global $testsystem, $system;
$testsystem = new Config();
$testsystem->setFolders("src", "");
$system = &$testsystem;

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (headers_sent() == false) {
        session_start();
    }
}
ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');