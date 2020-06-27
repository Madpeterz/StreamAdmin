<?php
// requires framework_mysqli
if(defined("require_id_on_load") == false)
{
    define("require_id_on_load",false);
}
$framework_loading = array(
    array(
        "folder" => "",
        "files" => array("error_logging"),
    ),
    array(
        "folder" => "genClass",
        "files" => array("core","get","set","load","db","final"),
    ),
    array(
        "folder" => "genClass_set",
        "files" => array("core","get","bulk.update","bulk.remove","load","functions","final"),
    )
);
if(isset($db_objects_load_path) == false)
{
    $db_objects_load_path = dirname(__FILE__)."/";
}
foreach($framework_loading as $loading_area)
{
    $load_path = $db_objects_load_path;
    if($loading_area["folder"] != "")
    {
        $load_path .= $loading_area["folder"];
        $load_path .= "/";
    }
    foreach($loading_area["files"] as $load_file)
    {
        require_once($load_path.$load_file.".php");
    }
}
?>
