<?php

namespace App;

use App\Switchboard\CronTab;

if (defined("TESTING") == false) {
    chdir(__DIR__);
    if (defined("APPFOLDER") == false) {
        define("APPFOLDER", "../App/");
    }
    include APPFOLDER . "../../vendor/autoload.php";
    set_time_limit(65);

    global $system;
    $system = new Config();
    $system->run();
}


new CronTab();
