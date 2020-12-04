<?php

namespace App;

use YAPF\InputFilter\InputFilter as InputFilter;

if (defined("CORRECT") == true) {
    include "../App/Framework/load.php";
    add_vendor("website");
    include "theme/streamadminr5/layout/install/template.php";
    $input = new InputFilter();
    if ($module == "owner") {
        include "../App/Framework/installer/owner.php";
    } elseif ($module == "test") {
        include "../App/Framework/installer/test.php";
    } elseif ($module == "install") {
        include "../App/Framework/installer/install.php";
    } elseif ($module == "setup") {
        include "../App/Framework/installer/setup.php";
    } elseif ($module == "final") {
        include "../App/Framework/installer/final.php";
    } else {
        include "../App/Framework/installer/dbconfig.php";
    }
    $this->output->render_page();
} else {
    die("Please do not attempt to run installer directly it will break something!");
}
