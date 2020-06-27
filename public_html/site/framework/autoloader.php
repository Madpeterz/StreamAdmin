<?php
function auto_load_model($class_name="")
{
	$try_class_file = "";
	$bits = explode("_",$class_name);
    if(count($bits) >= 2)
    {
        if($bits[count($bits)-1] != "helper")
        {
            if($bits[count($bits)-1] == "set") array_pop($bits);
            $try_class_file = implode("_",$bits).".php";
        }
    }
    else $try_class_file = $bits[0].".php";
	if($try_class_file != "")
	{
		$loadfile = "site/model/".$try_class_file."";
		if(file_exists($loadfile)) require_once($loadfile);
		else auto_load_helper($class_name);
	}
	else auto_load_helper($class_name);
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
?>
