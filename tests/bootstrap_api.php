<?php

namespace Tests;

use YAPF\MySQLi\MysqliEnabled;

if (defined("ROOTFOLDER") == false) {
    define("ROOTFOLDER", "src");
}
if (defined("DEEPFOLDERPATH") == false) {
    define("DEEPFOLDERPATH", ".");
}
if (defined("UNITTEST") == false) {
    define("UNITTEST", "yep");
}
include ROOTFOLDER . "/App/Framework/load.php";