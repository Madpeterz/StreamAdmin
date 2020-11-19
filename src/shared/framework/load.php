<?php

namespace App;

session_start();
define("REQUIRE_ID_ON_LOAD", true);
$sql = null;
$session = null;
$slconfig = null;

include "../../vendor/autoload.php";
include "../shared/config/db.php";

include "../shared/framework/functions.php";
include "../shared/framework/globals.php";
include "../shared/framework/url_loading.php";
include "../shared/framework/session.php";
include "../shared/framework/autoloader.php";
include "../shared/framework/forms.php";
include "3rdparty/3rdparty.php";
include "../shared/framework/pageworks.php";

include "../shared/config/site.php";

if (class_exists("Db", false) == true) {
    $sql = new MysqliConnector();

    if (defined("installed") == true) {
        // lets get some work done
        $session = new session_control();
        $slconfig = new slconfig();
        if ($slconfig->loadID(1) == true) {
            $session->load_from_session();
        } else {
            die("Unable to load system config [PANIC]");
        }
        if ($slconfig != null) {
            $timezone_config_from_cache = $this->output->get_cache_file("current_timezone", false);
            if ($timezone_config_from_cache == null) {
                $timezone = new timezones();
                if ($timezone->loadID($slconfig->get_displaytimezonelink()) == true) {
                    $cooked = $timezone_name . "###" . $timezone->get_code();
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
