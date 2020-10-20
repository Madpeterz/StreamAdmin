<?php
session_start();
$sql = null;
$session = null;
$slconfig = null;
include("site/framework/core.php");
if(install_ok() == true)
{

    include("site/config/load.php"); // sql_config
    require_once("site/framework/mysqli/src/loader.php"); // sql_driver

    // lets get some work done.
    $sql = new mysqli_controler();
    $session = new session_control();
    $slconfig = new slconfig();
    if($slconfig->load(1) == true)
    {
        $session->load_from_session();
        $timezone = new timezones();
        if($timezone->load($slconfig->get_displaytimezonelink()) == true)
        {
            $timezone_name = $timezone->get_name();
            date_default_timezone_set($timezone->get_code());
        }
    }
    else
    {
        die("Unable to load system config [PANIC]");
    }
}
else
{
    include("installer/config.php");
}
include("site/framework/pageworks.php");
?>
