<?php
define("require_id_on_load",true);
require_once("site/vendor/yetonemorephpframework/db_objects/loader.php"); // db_objects
$framework_loading = array("functions","globals","url_loading","session","inputFilter","autoloader","forms","api");
foreach($framework_loading as $framework) { require_once("site/framework/".$framework.".php"); }
include("site/vendor/vendor.php");
?>
