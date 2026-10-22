<?php

declare(strict_types=1);

namespace Tests;

use App\Config;

define("UNITTEST", "yep");

require "./vendor/autoload.php";
require "test.db.php";
require "./src/App/Framework/Functions.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (headers_sent() == false) {
        session_start();
    }
}
ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

$system = new Config();
installDatabase($system);
resetDatabase($system);

function installDatabase(\App\Config $system): void
{
    $wipeandmake = [];
    $wipeandmake[] = "DROP DATABASE IF EXISTS `test`;";
    $wipeandmake[] = "CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
    $wipeandmake[] = "USE test;";
    $wipeandmake[] = "SET FOREIGN_KEY_CHECKS = 1;";
    $system->getSQL()->dbName = null; // use test as the entry db to connect and switch as part of wipe and make
    $system->getSQL()->rawSQL(null, $wipeandmake); // wipe the database if it exists
    $system->getSQL()->rawSQL("Versions/installer.sql"); // install the base sql
    $system->getSQL()->rawSQL("Versions/2.0.1.1.sql"); // install the newerst sql
    $system->getSQL()->dbName = "test";
    $system->getSQL()->sqlSave(true);
}
function resetDatabase(\App\Config $system): void
{
    // wipe and make a new test database
    $repply = $system->getSQL()->rawSQL("Tests/reset.db.sql");
    if($repply->status == false) {
        die("[DB Reset] Could not reset the test database because: " . $repply->message);
    }
    $system->getSQL()->sqlSave(true);
}

