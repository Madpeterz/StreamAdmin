<?php
define("require_id_on_load",true);
include("site/framework/pageworks.php");
require_once("site/framework/db_objects/src/loader.php"); // db_objects
$framework_loading = array("functions","globals","url_loading","session","autoloader","forms");
foreach($framework_loading as $framework) { require_once("site/framework/".$framework.".php"); }
include("site/vendor/vendor.php");
?>
