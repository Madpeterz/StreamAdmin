<?php 

namespace Tests;

use App\Config;

define("UNITTEST", "yep");

include "./vendor/autoload.php";
include "test.db.php";
global $system;
$system = new Config();