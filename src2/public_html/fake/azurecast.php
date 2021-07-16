<?php

namespace App;

use tests\Faker\Azurecast;

if (defined("ROOTFOLDER") == true) {
    include_once DEEPFOLDERPATH . "/../vendor/autoload.php";
} else {
    include "../../../vendor/autoload.php";
}

define("UNITTEST", true);
new Azurecast();
