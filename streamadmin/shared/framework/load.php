<?php
session_start();
define("require_id_on_load",true);
$sql = null;
$session = null;
$slconfig = null;

include "shared/framework/db_objects/src/loader.php";

include "shared/config/db.php";
include "shared/framework/mysqli/src/loader.php";

include "shared/framework/functions.php";
include "shared/framework/globals.php";
include "shared/framework/url_loading.php";
include "shared/framework/session.php";
include "shared/framework/autoloader.php";
include "shared/framework/forms.php";
include "webpanel/vendor/vendor.php";
include "shared/framework/pageworks.php";

include "shared/config/site.php";

$sql = new mysqli_controler();

if(defined("installed") == true)
{
    // lets get some work done
    $session = new session_control();
    $slconfig = new slconfig();
    if($slconfig->load(1) == true)
    {
        $session->load_from_session();
    }
    else
    {
        die("Unable to load system config [PANIC]");
    }
    if($slconfig != null)
    {
        $timezone_config_from_cache = $view_reply->get_cache_file("current_timezone",false);
        if($timezone_config_from_cache == null)
        {
            $timezone = new timezones();
            if($timezone->load($slconfig->get_displaytimezonelink()) == true)
            {
                $cooked = $timezone_name."###".$timezone->get_code();
                $view_reply->set_cache_file($cooked,"current_timezone",false);
            }
            $timezone_config_from_cache = $view_reply->get_cache_file("current_timezone",false);
        }
        if($timezone_config_from_cache != null)
        {
            $bits = explode("###",$timezone_config_from_cache);
            $timezone_name = $bits[0];
            date_default_timezone_set($bits[1]);
        }
    }
}
?>
