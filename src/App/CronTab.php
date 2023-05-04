<?php

namespace App;

use App\Switchboard\CronTab;
use App\Config;

#define("ERRORCONSOLE", "yes");
#define("ERRORCONSOLEPRINT", "yes");

if (defined("TESTING") == false) {
    chdir(__DIR__);
    if (defined("APPFOLDER") == false) {
        define("APPFOLDER", "../App/");
    }
    include APPFOLDER . "../../vendor/autoload.php";
    include APPFOLDER . "Framework/Functions.php";
    set_time_limit(65);

    global $system;
    $system = new Config();
    $system->run();
}

new CronTab();
