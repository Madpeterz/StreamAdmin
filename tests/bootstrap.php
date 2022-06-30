<?php 

namespace Tests;

if (defined("ROOTFOLDER") == false) {
    define("ROOTFOLDER", "src");
}
if (defined("DEEPFOLDERPATH") == false) {
    define("DEEPFOLDERPATH", ".");
}
if (defined("UNITTEST") == false) {
    define("UNITTEST", "yep");
}
if(file_exists("src/App/Config/db_installed.php") == true) {
    unlink("src/App/Config/db_installed.php");
}
if(file_exists("src/App/Config/site_installed.php") == true) {
    unlink("src/App/Config/site_installed.php");
}
if(file_exists("src/App/Config/ready.txt") == true) {
    unlink("src/App/Config/ready.txt");
}
include "vendor/autoload.php";
include ROOTFOLDER . "/App/Framework/load.php";