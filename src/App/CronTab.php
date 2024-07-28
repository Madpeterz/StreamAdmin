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

$opts = [];
foreach ($_SERVER["argv"] as $argKey => $argValue) {
    $value = $argValue;
    $key = $argKey;
    if (preg_match('@\-\-(.+)=(.+)@', $argValue, $matches)) {
        $key = $matches[1];
        $value = $matches[2];
    } elseif (preg_match('@\-\-(.+)@', $argValue, $matches)) {
        $key = $matches[1];
        $value = true;
    } elseif (preg_match('@\-(.+)=(.+)@', $argValue, $matches)) {
        $key = $matches[1];
        $value = $matches[2];
    } elseif (preg_match('@\-(.+)@', $argValue, $matches)) {
        $key = $matches[1];
        $value = true;
    }
    $opts[$key] = $value;
}
if (array_key_exists("d", $opts) == false) {
    print "d value not set\n";
    die();
}
if (array_key_exists("t", $opts) == false) {
    print "d value not set\n";
    die();
}
$delay = intval($opts["d"]);
if (($delay < 1) || ($delay > 55)) {
    print "d value not in a vaild range\n";
    die();
}
$objectmode = "";
if ($opts["t"] == "Botcommandq") {
    $objecttaskid = 1;
    $objectmode = "botcommandqserver";
} elseif ($opts["t"] == "Detailsserver") {
    $objecttaskid = 2;
    $objectmode = "detailsserver";
} elseif ($opts["t"] == "Dynamicnotecards") {
    $objecttaskid = 3;
    $objectmode = "notecardsserver";
}
if ($objectmode == "") {
    print "t value not supported\n";
    die();
}
print "\n";
print $objectmode . " waiting for " . $delay . " to trigger\n";
sleep($delay);
new CronTab();
print "\n";
