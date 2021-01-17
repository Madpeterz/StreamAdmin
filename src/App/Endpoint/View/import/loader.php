<?php

$this->setSwapTag("html_title", "R4 import");
$this->setSwapTag("page_title", "Import /");
$this->setSwapTag("page_actions", "");
if ($session->getOwnerLevel() == 1) {
    function auto_load_r4_model($class_name = ""): void
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
    if (file_exists("../App/Config/r4.php") == true) {
        include "../App/Config/r4.php";
    } else {
        $area = "setup";
    }
} else {
    $this->output->redirect("?message=Sorry only the system owner can access this area");
}
