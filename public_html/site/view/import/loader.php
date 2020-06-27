<?php
$template_parts["page_actions"] = "";
$template_parts["html_title"] = "R4 import";
$template_parts["page_title"] = "Import / ";
if($session->get_ownerlevel() == 1)
{
    function auto_load_r4_model($class_name="")
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
        $try_class_file = str_replace("r4_","",$try_class_file);
    	if($try_class_file != "")
    	{
    		$loadfile = "site/r4_model/".$try_class_file."";
    		if(file_exists($loadfile)) require_once($loadfile);
    	}
    }
    spl_autoload_register('auto_load_r4_model');
    $template_parts["html_title"] = "Import";


    if(file_exists("site/config/r4.php") == true)
    {
        $check_file = "site/view/import/".$area.".php";
        if(file_exists($check_file) == true)
        {
            $template_parts["page_title"] = "Import";
            include("site/config/r4.php");
            include($check_file);
        }
    }
    else
    {
        include("site/view/import/setup.php");
    }
}
else
{
    redirect("?message=Sorry only the system owner can access this area");
}
?>
