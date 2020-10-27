<?php
session_start();
$sql = null;
$session = null;
$slconfig = null;
include "shared/framework/core.php";
if(install_ok() == true)
{
    include "shared/framework/core.php"; // sql_config
    require_once("shared/framework/mysqli/src/loader.php"); // sql_driver
    // lets get some work done.
    $sql = new mysqli_controler();
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
}
else
{
    include "shared/framework/core.php";
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
?>
