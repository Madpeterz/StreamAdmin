<?php

$view_reply->set_swap_tag_string("html_title", "R4 import");
$view_reply->set_swap_tag_string("page_title", "Import /");
$view_reply->set_swap_tag_string("page_actions", "");
if ($session->get_ownerlevel() == 1) {
    function auto_load_r4_model($class_name = "")
    {
        $try_class_file = "";
        $bits = explode("_", $class_name);
        if (count($bits) >= 2) {
            if ($bits[count($bits) - 1] != "helper") {
                if ($bits[count($bits) - 1] == "set") {
                    array_pop($bits);
                }
                $try_class_file = implode("_", $bits) . ".php";
            }
        } else {
            $try_class_file = $bits[0] . ".php";
        }
        $try_class_file = str_replace("r4_", "", $try_class_file);
        if ($try_class_file != "") {
            $loadfile = "shared/r4_model/" . $try_class_file . "";
            if (file_exists($loadfile)) {
                require_once($loadfile);
            }
        }
    }
    spl_autoload_register('auto_load_r4_model');
    if (file_exists("../shared/config/r4.php") == true) {
        include "../shared/config/r4.php";
    } else {
        $area = "setup";
    }
} else {
    $view_reply->redirect("?message=Sorry only the system owner can access this area");
}
