<?php
$view_reply->set_swap_tag_string("html_title","R4 import");
$view_reply->set_swap_tag_string("page_title","Import /");
$view_reply->set_swap_tag_string("page_actions","");
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
    if(file_exists("site/config/r4.php") == true)
    {
        $check_file = "site/view/import/".$area.".php";
        if(file_exists($check_file) == true)
        {
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
    $view_reply->redirect("?message=Sorry only the system owner can access this area");
}
?>
