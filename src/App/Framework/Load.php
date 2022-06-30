<?php

use App\Config;

@ini_set('display_errors', 1);
@ini_set('log_errors', 0);
@ini_set('session.gc_maxlifetime', ((60 * 60) * 2));
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (headers_sent() == false) {
        session_start();
    }
}
include "../../vendor/autoload.php";

global $system;
$system = new Config();
$system->run();
