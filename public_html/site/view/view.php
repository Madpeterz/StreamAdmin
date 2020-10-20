<?php
function load_module_view() : ?bool
{
    global $view_reply, $module;
    $test_path = "site/view/".$module."/loader.php";
    if(file_exists($test_path) == true)
    {
        include($test_path); // run normal loader
        $check_file = "site/view/".$module."/".$area.".php";
        if(file_exists($check_file) == true)
        {
            include($check_file); // run targeted area
        }
        return true;
    }
    else
    {
        $view_reply->set_swap_tag_string("html_title","Oh snap");
        $view_reply->set_swap_tag_string("page_title","Oh snap");
        $view_reply->set_swap_tag_string("page_actions","- ERROR -");
        $view_reply->set_swap_tag_string("page_content","Unable to load ".$module." ".$file."");
    }
    return false;
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
load_module_view();
$view_reply->render_page();
?>
