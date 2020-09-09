<?php
function auto_load_model($class_name="")
{
	$bits = explode("_",$class_name);
	if(in_array("set",$bits) == true)
	{
		array_pop($bits);
		$class_name = implode("_",$bits);
	}
	$loadfile = "site/model/".$class_name.".php";
	if(file_exists($loadfile)) require_once($loadfile);
}
function auto_load_api($class_name="")
{
	$loadfile = "site/serverapis/".$class_name.".php";
	if(file_exists($loadfile)) require_once($loadfile);
}
function auto_load_helper($class_name="")
{
	$bits = explode("_",$class_name);
	array_pop($bits);
	$try_class_file = implode("_",$bits);
	$loadfile = "site/model_helpers/".$try_class_file.".helper.php";
	if(file_exists($loadfile)) require_once($loadfile);
}
spl_autoload_register('auto_load_model');
spl_autoload_register('auto_load_helper');
spl_autoload_register('auto_load_api');
?>
