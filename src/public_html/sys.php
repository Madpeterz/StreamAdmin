<?php

namespace App;

use App\Switchboard\Sys;

if (defined("ROOTFOLDER") == true) {
    include ROOTFOLDER . "/App/Framework/load.php";
} else {
    include "../App/Framework/load.php";
}

new Sys();
