<?php

namespace App;

use tests\Faker\Centova3_2_12;

if (defined("ROOTFOLDER") == true) {
    include_once DEEPFOLDERPATH . "/../vendor/autoload.php";
} else {
    include "../../../vendor/autoload.php";
}

define("UNITTEST", true);
new Centova3_2_12();
