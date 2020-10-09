<?php
function get_require_path(string $module="",string $file="",bool $allow_downgrade=true) : ?string
{
    global $view_reply;
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
            $view_reply->set_swap_tag_string("html_title","Oh snap");
            $view_reply->set_swap_tag_string("page_title","Oh snap");
            $view_reply->set_swap_tag_string("page_actions","- ERROR -");
            $view_reply->set_swap_tag_string("page_content","Unable to load ".$module." ".$file."");
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
    include("site/theme/streamadminr5/layout/sidemenu/template.php");
    if($area == "") $area = "default";
    require_once("site/view/shared/menu.php");
}
else
{
    include("site/theme/streamadminr5/layout/full/template.php");
}
$found_path = get_require_path($module,"",false);
if($found_path != null)
{
    $view_reply->set_swap_tag_string("html_title",$module." / ");
    require_once($found_path);
}
$view_reply->render_page();
?>
