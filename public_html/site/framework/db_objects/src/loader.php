<?php
if(defined("require_id_on_load") == false)
{
    define("require_id_on_load",false);
}
if(isset($db_objects_load_path) == false)
{
    $db_objects_load_path = dirname(__FILE__)."/";
}
include($db_objects_load_path."error_logging.php");
include($db_objects_load_path."filter/loader.php");
include($db_objects_load_path."genClass/core.php");
include($db_objects_load_path."genClass/get.php");
include($db_objects_load_path."genClass/set.php");
include($db_objects_load_path."genClass/load.php");
include($db_objects_load_path."genClass/db.php");
include($db_objects_load_path."genClass/final.php");
include($db_objects_load_path."genClass_set/core.php");
include($db_objects_load_path."genClass_set/get.php");
include($db_objects_load_path."genClass_set/bulk.update.php");
include($db_objects_load_path."genClass_set/bulk.remove.php");
include($db_objects_load_path."genClass_set/load.php");
include($db_objects_load_path."genClass_set/functions.php");
include($db_objects_load_path."genClass_set/final.php");
?>
