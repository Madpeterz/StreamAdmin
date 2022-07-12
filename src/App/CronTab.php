<?php

namespace App;

use App\Switchboard\CronTab;

if (defined("TESTING") == false) {
    include("../../vendor/autoload.php");
    set_time_limit(60);

    $system = new Config();
    $system->setFolders("", "../");
}


new CronTab();
