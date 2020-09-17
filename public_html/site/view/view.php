<?php
function get_require_path(string $module="",string $file="",bool $allow_downgrade=true) : ?string
{
    if($module == "login")
    {
        return "site/view/login/full.php";
    }
    else
    {
        $test_path = "site/view/";
        if($module != "") $test_path .= "/".$module;
        if($file != "") $test_path .= "/".$file;
        else $test_path .= "/loader.php";
        if(file_exists($test_path) == true)
        {
            return $test_path;
        }
        else
        {
            $template_parts["page_title"] = "Oh snap";
            $template_parts["page_actions"] = "- ERROR -";
            return null;
        }
    }
}
include("site/framework/loader_light.php");
add_vendor("website");
if($session->get_logged_in() == false)
{
    $module = "login";
}
if($module != "login")
{
    load_template("sidemenu");
    if($area == "") $area = "default";
    require_once("site/view/shared/menu.php");
}
else
{
    load_template("full");
}
$buffer = ob_get_contents();
ob_clean();
$found_path = get_require_path($module,"",false);
if($found_path != null) require_once($found_path);
else print "Unable to load page<br/>Please try again later";
$template_parts["page_content"] = ob_get_contents();
ob_clean();
foreach($template_parts as $key => $value)
{
    $buffer = str_replace("[[".$key."]]",$value,$buffer);
}
$buffer = str_replace("[[MODULE]]",$page,$buffer);
$buffer = str_replace("[[AREA]]",$optional,$buffer);
$buffer = str_replace("[[PAGE]]",$page,$buffer);
foreach($template_parts as $key => $value)
{
    $buffer = str_replace("[[".$key."]]",$value,$buffer);
}
$buffer = str_replace("[[MODULE]]",$page,$buffer);
$buffer = str_replace("[[AREA]]",$optional,$buffer);
$buffer = str_replace("[[PAGE]]",$page,$buffer);
$buffer = str_replace("@NL@","\r\n",$buffer);
print $buffer;
?>
