<?php

namespace Tests;

use App\Config;
use PHPUnit\Framework\TestCase;

class TestWorker extends TestCase
{
    public function setUp(): void {}
    public function resetPost()
    {
        global $_POST;
        $_POST = [];
    }
    public static function tearDownAfterClass(): void
    {
        global $system;
        $classname = get_called_class();
        $bits = explode("\\", $classname);
        $classname = end($bits);
        $cleanup = [
            "DROP DATABASE `" . $classname . "`;"
        ];
        $system->getSQL()->sqlRollBack();
        $system->shutdown();
    }
    public static function setUpBeforeClass(): void
    {
        $wipetables = [
            "apirequests",
            "apis",
            "avatar",
            "banlist",
            "botconfig",
            "detail",
            "event",
            "message",
            "notecard",
            "notice",
            "noticenotecard",
            "objects",
            "package",
            "region",
            "rental",
            "reseller",
            "server",
            "servertypes",
            "slconfig",
            "staff",
            "stream",
            "auditlog",
            "template",
            "textureconfig",
            "timezones",
            "transactions",
            "treevender",
            "treevenderpackages",
            "alltypestable",
            "counttoonehundo",
            "endoftestempty",
            "endoftestwithfourentrys",
            "endoftestwithupdates",
            "flagedvalues",
            "liketests",
            "relationtestinga",
            "relationtestingb",
            "rollbacktest",
            "twintables1",
            "twintables2",
            "weirdtable",
            "eventsq",
            "datatable",
            "notecardmail",
            "botcommandq",
            "rentalnoticeptout",
            "marketplacecoupons",
            "coupons"

        ];
        global $system;
        $system = new Config();
        $system->setFolders("src", "");
        $classname = get_called_class();
        $bits = explode("\\", $classname);
        $classname = end($bits);
        $system->getSQL()->dbName = $classname;
        $wipeandmake = [];
        $wipeandmake[] = "CREATE DATABASE IF NOT EXISTS `" . $system->getSQL()->dbName . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
        $wipeandmake[] = "USE " . $system->getSQL()->dbName . ";";
        $wipeandmake[] = "SET FOREIGN_KEY_CHECKS = 0;";
        $wipeandmake[] = "DROP TABLE IF EXISTS `" . implode("`,`", $wipetables) . "`;";
        $wipeandmake[] = "SET FOREIGN_KEY_CHECKS = 1;";
        $system->getSQL()->dbName = null; // use test as the entry db to connect and switch as part of wipe and make
        $system->getSQL()->rawSQL(null, $wipeandmake); // wipe the database if it exists
        $system->getSQL()->rawSQL("Versions/installer.sql"); // install the base sql
        $system->getSQL()->rawSQL("Versions/2.0.1.0.sql"); // install any updates
        $system->getSQL()->dbName = $classname;
    }
}
