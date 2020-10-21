<?php
function load_module_view() : array
{
    global $view_reply, $module, $area;
    $target_files = array();
    $test_path = "site/view/".$module."/loader.php";
    if(file_exists($test_path) == true)
    {
        $target_files[] = $test_path;
        $check_file = "site/view/".$module."/".$area.".php";
        if(file_exists($check_file) == true)
        {
            $target_files[] = $check_file;
        }
    }
    else
    {
        $view_reply->set_swap_tag_string("html_title","Oh snap");
        $view_reply->set_swap_tag_string("page_title","Oh snap");
        $view_reply->set_swap_tag_string("page_actions","- ERROR -");
        $view_reply->set_swap_tag_string("page_content","Unable to load ".$module." ".$file."");
    }
    return $target_files;
}
include("site/framework/loader_light.php");
add_vendor("website");
if($session->get_logged_in() == false)
{
    $module = "login";
}
if($module != "login")
{
    include("site/theme/".$site_theme."/layout/sidemenu/template.php");
    if($area == "") $area = "default";
    require_once("site/view/shared/menu.php");
}
else
{
    include("site/theme/".$site_theme."/layout/full/template.php");
}
$load_files = load_module_view();
foreach($load_files as $file)
{
    include($file);
}
$view_reply->render_page();
?>
