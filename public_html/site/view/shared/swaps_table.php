<?php
$example_time = time();
$example_time += $unixtime_week + rand(1000,5000);
$swaps = array(
    "AVATAR_FIRSTNAME" => "Madpeter",
    "AVATAR_LASTNAME" => "Zond",
    "AVATAR_FULLNAME" => "Madpeter Zond",
    "RENTAL_EXPIRES_DATETIME" => date('l jS \of F Y h:i:s A',$example_time),
    "RENTAL_TIMELEFT" => timeleft_hours_and_days($example_time),
    "STREAM_PORT" => 4000,
    "STREAM_ADMINUSERNAME" => "SuperAdmin",
    "STREAM_ADMINPASSWORD" => "AdminPaSSwordHere",
    "STREAM_DJPASSWORD" => "DJpasswordYo",
    "STREAM_MOUNTPOINT" => "/live",
    "SERVER_DOMAIN" => "http://livestreamservice.demo",
    "SERVER_CONTROLPANEL" => "https://livestreamservice.demo:5000",
    "PACKAGE_NAME" => "CheapWeeklyPackage",
    "PACKAGE_LISTENERS" => 10,
    "PACKAGE_BITRATE" => 56,
    "PACKAGE_AUTODJ" => "Enabled",
    "PACKAGE_AUTODJ_SIZE" => 3,
    "PACKAGE_UID" => "XXXXXXXX",
    "RENTAL_UID" => "XXXXXXXX",
    "NL" => "~ Creates a new line ~"

);
$table_head = array("id","Swaptag","Example");
$table_body = array();
$loop = 0;
foreach($swaps as $key => $value)
{
    $entry = array();
    $entry[] = $loop;
    $entry[] = "[[".$key."]]";
    $entry[] = $value;
    $table_body[] = $entry;
    $loop++;
}
echo render_datatable($table_head,$table_body);
?>
