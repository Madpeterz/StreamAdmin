<?php

namespace App\CronJob;

if (defined("ROOTFOLDER") == false) {
    include "CronTabFlags.php";
}

include ROOTFOLDER . "/App/Framework/load.php";

set_time_limit(57);

$options = get_opts();
if (array_key_exists("t", $options) == false) {
    echo "task arg t is missing unable to continue: " . json_encode($options);
    die();
}

$taskPicker = "App\\CronJob\\Tasks\\" . $options["t"];
if (class_exists($taskPicker) == false) {
    echo  "task arg t of " . $options["t"] . " is not supported by crontab" . json_encode($options);
    die();
}

$groups = 15;
if (defined("TESTING") == true) {
    $groups = 1;
    // when unit testing we only want to run the cron once so we dont get stuck for ages.
}
$worker = new $taskPicker($groups);
