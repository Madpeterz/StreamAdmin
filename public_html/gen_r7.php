<?php
/*
    Please move this file up a level out of the gen folder before running ^+^
*/
// setup workarea
define("framework_location","site/vendor/yetonemorephpframework/"); // db_objects and mysqli
define("gen_database_host","localhost");
define("gen_database_username","root");
define("gen_database_password","");
define("save_models_to_folder","versions/modal/1.0.0.4/");
//define("add_db_to_table",true); // add the database name before the table name
//define("source_databases",array("streamadmin","secondbothost"));

define("add_db_to_table",false); // add the database name before the table name
define("source_databases",array("streamadmin"));

// load framework
require_once("".framework_location."db_objects/loader.php"); // db_objects
include("gen/create_db_class.php");
require_once("".framework_location."mysqli/loader.php"); // sql_driver

// connect to SQL
$sql = new mysqli_controler();

// lets rock
include("gen/gen.php");
?>
