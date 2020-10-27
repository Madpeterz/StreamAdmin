<?php
function auto_load_model($class_name="")
{
	$bits = explode("_",$class_name);
	if(in_array("set",$bits) == true)
	{
		array_pop($bits);
		$class_name = implode("_",$bits);
	}
	$loadfile = "shared/model/".$class_name.".php";
	if(file_exists($loadfile)) require_once($loadfile);
}
function auto_load_api($class_name="")
{
	$loadfile = "shared/media_server_apis/".$class_name.".php";
	if(file_exists($loadfile))
	{
		require_once($loadfile);
	}
	else
	{
		$loadfile = "shared/media_server_apis/abstracts/".$class_name.".php";
		if(file_exists($loadfile))
		{
			require_once($loadfile);
		}
	}
}
function auto_load_helper($class_name="")
{
	$bits = explode("_",$class_name);
	if(in_array("helper",$bits) == true)
	{
		array_pop($bits);
		$class_name = implode("_",$bits);
		$loadfile = "shared/model_helpers/".$class_name.".helper.php";
		if(file_exists($loadfile)) require_once($loadfile);
	}
}
spl_autoload_register('auto_load_model');
spl_autoload_register('auto_load_helper');
spl_autoload_register('auto_load_api');
?>
