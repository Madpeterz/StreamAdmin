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
        $this->output->setSwapTagString("html_title", "Oh snap");
        $this->output->setSwapTagString("page_title", "Oh snap");
        $this->output->setSwapTagString("page_actions", "- ERROR -");
        $this->output->setSwapTagString("page_content", "Unable to load " . $module . " " . $file . "");
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
$this->output->render_page();
