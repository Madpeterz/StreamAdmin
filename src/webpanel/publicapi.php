<?php

namespace App;

ob_start();
include "../shared/framework/install.php";
if (defined("installed") == true) {
    include "endpoints/api_public/loader.php";
} else {
    echo "disabled - install mode";
}
ob_end_flush();
