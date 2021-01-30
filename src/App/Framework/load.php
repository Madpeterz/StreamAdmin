<?php

namespace App;

use App\Framework\SessionControl;
use App\R7\Model\Slconfig;
use App\R7\Model\Timezones;
use App\Template\Output\Cache;
use YAPF\MySQLi\MysqliEnabled;

global $slconfig, $session, $sql, $timezone_name;

if (defined("ROOTFOLDER") == false) {
    include_once "../App/Flags/DefaultFolders.php";
}
ini_set('display_errors', 1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (headers_sent() == false) {
        session_start();
    }
}
include_once ROOTFOLDER . "/App/Framework/globals.php";
include_once ROOTFOLDER . "/App/Framework/url_loading.php";
include_once DEEPFOLDERPATH . "/vendor/autoload.php";
include_once ROOTFOLDER . "/App/Config/REQUIRE_ID_ON_LOAD.php";
include_once ROOTFOLDER . "/App/Framework/install.php";
include_once ROOTFOLDER . "/App/Config/load.php";
include_once ROOTFOLDER . "/App/Framework/functions.php";
$session = new SessionControl();
if (install_ok() == true) {
    if (class_exists("App\\Db", false) == true) {
        $sql = new MysqliEnabled();
        if (defined("INSTALLED") == true) {
            // lets get some work done
            $slconfig = new Slconfig();
            if ($slconfig->loadID(1) == true) {
                $session->loadFromSession();
            } else {
                die("Unable to load system config [PANIC]");
            }
            if ($slconfig != null) {
                $catchereader = new Cache();
                $timezone_config_from_cache =  $catchereader->getCacheFile("current_timezone", false);
                if ($timezone_config_from_cache == null) {
                    $timezone = new Timezones();
                    if ($timezone->loadID($slconfig->getDisplayTimezoneLink()) == true) {
                        $cooked = $timezone->getName() . "###" . $timezone->getCode();
                        $catchereader->setCacheFile($cooked, "current_timezone", false);
                    }
                    $timezone_config_from_cache = $catchereader->getCacheFile("current_timezone", false);
                }
                if ($timezone_config_from_cache != null) {
                    $bits = explode("###", $timezone_config_from_cache);
                    $timezone_name = $bits[0];
                    date_default_timezone_set($bits[1]);
                }
            }
        }
    }
}
