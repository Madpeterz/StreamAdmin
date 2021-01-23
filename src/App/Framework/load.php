<?php

namespace App;

use App\Framework\SessionControl;
use App\Models\Slconfig;
use App\Models\Timezones;
use YAPF\MySQLi\MysqliEnabled;

ini_set('display_errors', 1);
session_start();
include "../App/Framework/globals.php";
include "../App/Framework/url_loading.php";
include "../../vendor/autoload.php";
include "../App/Config/REQUIRE_ID_ON_LOAD.php";
include "../App/Framework/install.php";
include "../App/Config/load.php";
include "../App/Framework/functions.php";
$session = new SessionControl();
$slconfig = new Slconfig();
if (install_ok() == true) {
    $sql = new MysqliEnabled();
    if (class_exists("Db", false) == true) {
        if (defined("INSTALLED") == true) {
            // lets get some work done

            if ($slconfig->loadID(1) == true) {
                $session->loadFromSession();
            } else {
                die("Unable to load system config [PANIC]");
            }
            if ($slconfig != null) {
                $timezone_config_from_cache = $this->output->get_cache_file("current_timezone", false);
                if ($timezone_config_from_cache == null) {
                    $timezone = new Timezones();
                    if ($timezone->loadID($slconfig->getDisplayTimezoneLink()) == true) {
                        $cooked = $timezone_name . "###" . $timezone->getCode();
                        $this->output->set_cache_file($cooked, "current_timezone", false);
                    }
                    $timezone_config_from_cache = $this->output->get_cache_file("current_timezone", false);
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
