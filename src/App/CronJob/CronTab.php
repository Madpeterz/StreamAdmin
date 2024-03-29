<?php

namespace App\CronJob;

use App\CronJob\Tasks\ApiRequests;
use App\CronJob\Tasks\BotcommandQ;
use App\CronJob\Tasks\ClientAutoSuspend;
use App\CronJob\Tasks\DetailsServer;
use App\CronJob\Tasks\DynamicNotecards;
use App\CronJob\Tasks\ExportFlow1;

if (defined("ROOTFOLDER") == false) {
    // Running in classic?
    // make sure to change this path
    define("ROOTFOLDER", "/srv/website/src");
    define("DEEPFOLDERPATH", "/srv/website");
}

include ROOTFOLDER . "/App/Framework/load.php";

set_time_limit(60);

$options = get_opts();
if (array_key_exists("t", $options) == false) {
    echo "task arg t is missing unable to continue: " . json_encode($options);
    die();
}

$groups = 15;
if (defined("TESTING") == true) {
    $groups = 1;
    // when unit testing we only want to run the cron once so we dont get stuck for ages.
}

if ($options["t"] == "ApiRequests") {
    new ApiRequests($groups);
} elseif ($options["t"] == "BotcommandQ") {
    new BotcommandQ($groups);
} elseif ($options["t"] == "ClientAutoSuspend") {
    new ClientAutoSuspend($groups);
} elseif ($options["t"] == "DetailsServer") {
    new DetailsServer($groups);
} elseif ($options["t"] == "DynamicNotecards") {
    new DynamicNotecards($groups);
} elseif ($options["t"] == "Export1") {
    new ExportFlow1();
} else {
    die("Unknown cron job selected");
}
