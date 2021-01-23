<?php

$example_time = time();
$example_time += $unixtime_week + rand(1000, 5000);
$swaps = [
    "AVATAR_FIRSTNAME" => "Madpeter",
    "AVATAR_LASTNAME" => "Zond",
    "AVATAR_FULLNAME" => "Madpeter Zond",
    "RENTAL_EXPIRES_DATETIME" => date('l jS \of F Y h:i:s A', $example_time),
    "RENTAL_TIMELEFT" => timeleftHoursAndDays($example_time),
    "STREAM_PORT" => 4000,
    "STREAM_ADMINUSERNAME" => "SuperAdmin",
    "STREAM_ADMINPASSWORD" => "AdminPaSSwordHere",
    "STREAM_DJPASSWORD" => "DJpasswordYo",
    "STREAM_MOUNTPOINT" => "/live",
    "SERVER_DOMAIN" => "livestreamservice.demo (or) ip address",
    "SERVER_CONTROLPANEL" => "https://livestreamservice.demo:5000",
    "PACKAGE_NAME" => "CheapWeeklyPackage",
    "PACKAGE_LISTENERS" => 10,
    "PACKAGE_BITRATE" => 56,
    "PACKAGE_AUTODJ" => "Enabled",
    "PACKAGE_AUTODJ_SIZE" => 3,
    "PACKAGE_UID" => "XXXXXXXX",
    "RENTAL_UID" => "XXXXXXXX",
    "NL" => "~ Creates a new line ~",
    "TIMEZONE" => "Europe / London",
];

$table_head = ["Tag","Example","Tag","Example"];
$table_body = [];
$loop = 0;
$current = [];
foreach ($swaps as $key => $value) {
    $current[] = "[[" . $key . "]]";
    $current[] = $value;
    if (count($current) == 4) {
        $table_body[] = $current;
        $current = [];
    }
    $loop++;
}
if (count($current) != 0) {
    $current[] = " ";
    $current[] = " ";
    $table_body[] = $current;
}
$tableout = $this->renderTable($table_head, $table_body);
if (defined("swaps_table_paged") == true) {
    $pages["Swaps"] = $tableout;
} else {
    $this->output->addSwapTagString("page_content", $tableout);
}
