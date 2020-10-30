<?php

if (isset($dbObjectsLoadPath) == false) {
    $dbObjectsLoadPath = dirname(__FILE__) . "/";
}

if (defined("REQUIRE_ID_ON_LOAD") == false) {
    include $dbObjectsLoadPath . "SetRequireID.php";
}

include $dbObjectsLoadPath . "error_logging.php";
include $dbObjectsLoadPath . "SqlConnectedClass.php";

include $dbObjectsLoadPath . "genClass/Core.php";
include $dbObjectsLoadPath . "genClass/Get.php";
include $dbObjectsLoadPath . "genClass/Set.php";
include $dbObjectsLoadPath . "genClass/Load.php";
include $dbObjectsLoadPath . "genClass/DB.php";
include $dbObjectsLoadPath . "genClass/GenClass.php";

include $dbObjectsLoadPath . "genClass_set/core.php";
include $dbObjectsLoadPath . "genClass_set/get.php";
include $dbObjectsLoadPath . "genClass_set/bulk.update.php";
include $dbObjectsLoadPath . "genClass_set/bulk.remove.php";
include $dbObjectsLoadPath . "genClass_set/load.php";
include $dbObjectsLoadPath . "genClass_set/functions.php";
include $dbObjectsLoadPath . "genClass_set/final.php";

include $dbObjectsLoadPath . "filter/loader.php";
