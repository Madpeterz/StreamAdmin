<?php

function load_module_view(): array
{
    global $view_reply, $module, $area;
    $target_files = [];
    $test_path = "webpanel/view/" . $module . "/loader.php";
    if (file_exists($test_path) == true) {
        $target_files[] = $test_path;
        $check_file = "webpanel/view/" . $module . "/" . $area . ".php";
        if (file_exists($check_file) == true) {
            $target_files[] = $check_file;
        }
    } else {
        $view_reply->set_swap_tag_string("html_title", "Oh snap");
        $view_reply->set_swap_tag_string("page_title", "Oh snap");
        $view_reply->set_swap_tag_string("page_actions", "- ERROR -");
        $view_reply->set_swap_tag_string("page_content", "Unable to load " . $module . " " . $file . "");
    }
    return $target_files;
}
add_vendor("website");
if ($session->get_logged_in() == false) {
    $module = "login";
}
if ($module != "login") {
    include "theme/" . $site_theme . "/layout/sidemenu/template.php";
    if ($area == "") {
        $area = "default";
    }
    require_once("webpanel/view/shared/menu.php");
} else {
    include "theme/" . $site_theme . "/layout/full/template.php";
}
$load_files = load_module_view();
foreach ($load_files as $file) {
    include $file;
}
$view_reply->render_page();
