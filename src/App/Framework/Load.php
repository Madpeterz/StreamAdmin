<?php

use App\Config;

@ini_set('display_errors', "0");
@ini_set('log_errors', "1");
@ini_set('session.gc_maxlifetime', ((60 * 60) * 2));
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (headers_sent() == false) {
        session_start();
    }
}

if (defined("APPFOLDER") == false) {
    define("APPFOLDER", "../App/");
}

include APPFOLDER . "../../vendor/autoload.php";
include APPFOLDER . "Framework/Functions.php";

define("ERRORCONSOLE", "yes");
define("ERRORCONSOLEPRINT", "yes");
if (getenv('DB_HOST') !== false) {
    echo "Using live<br/>";
}
echo getenv('DB_HOST') . "<br/>";

global $system;
$system = new Config();
$system->run();
