<?php

namespace App;

set_time_limit(15);
include "../shared/framework/install.php";
if (defined("installed") == true) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include "control/loader.php";
    } else {
        include "view/view.php";
    }
} else {
    include "flags/correct.php";
    include "../shared/installer/index.php";
}
