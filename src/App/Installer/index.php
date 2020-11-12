<?php

namespace App;

use YAPF\InputFilter\InputFilter as InputFilter;

if (defined("CORRECT") == true) {
    include "../shared/framework/load.php";
    add_vendor("website");
    include "theme/streamadminr5/layout/install/template.php";
    $input = new InputFilter();
    if ($module == "owner") {
        include "../shared/installer/owner.php";
    } elseif ($module == "test") {
        include "../shared/installer/test.php";
    } elseif ($module == "install") {
        include "../shared/installer/install.php";
    } elseif ($module == "setup") {
        include "../shared/installer/setup.php";
    } elseif ($module == "final") {
        include "../shared/installer/final.php";
    } else {
        include "../shared/installer/dbconfig.php";
    }
    $view_reply->render_page();
} else {
    die("Please do not attempt to run installer directly it will break something!");
}
