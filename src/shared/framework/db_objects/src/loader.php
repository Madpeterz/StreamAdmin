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

include $dbObjectsLoadPath . "CollectionSet/Core.php";
include $dbObjectsLoadPath . "CollectionSet/Indexer.php";
include $dbObjectsLoadPath . "CollectionSet/Get.php";
include $dbObjectsLoadPath . "CollectionSet/BulkUpdate.php";
include $dbObjectsLoadPath . "CollectionSet/BulkRemove.php";
include $dbObjectsLoadPath . "CollectionSet/Load.php";
include $dbObjectsLoadPath . "CollectionSet/functions.php";
include $dbObjectsLoadPath . "CollectionSet/final.php";

include $dbObjectsLoadPath . "filter/loader.php";
