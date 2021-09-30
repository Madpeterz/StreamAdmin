<?php

function expiredAgo(
    $unixtime = 0,
    bool $use_secs = false,
    string $expiredWord = "Expired",
    string $activeWord = "Active"
): string {
    $dif = time() - $unixtime;
    if ($dif < 0) {
        return $activeWord;
    }
    return timeleftHoursAndDays(time() + $dif, $use_secs, $expiredWord);
}
/**
 * get_opts
 * @return mixed[]
 */
function get_opts(): array
{
    $opts = [];
    foreach ($_SERVER["argv"] as $k => $a) {
        if (preg_match('@\-\-(.+)=(.+)@', $a, $m)) {
            $opts[$m[1]] = $m[2];
        } elseif (preg_match('@\-\-(.+)@', $a, $m)) {
            $opts[$m[1]] = true;
        } elseif (preg_match('@\-(.+)=(.+)@', $a, $m)) {
            $opts[$m[1]] = $m[2];
        } elseif (preg_match('@\-(.+)@', $a, $m)) {
            $opts[$m[1]] = true;
        } else {
            $opts[$k] = $a;
        }
    }
    return $opts;
}
function timeleftHoursAndDays($unixtime = 0, bool $use_secs = false, string $expiredWord = "Expired"): string
{
    $dif = $unixtime - time();
    if ($dif <= 0) {
        return $expiredWord;
    }
    $mins = floor(($dif / 60));
    $hours = floor(($mins / 60));
    $days = floor($hours / 24);
    if ($days > 0) {
        $hours -= $days * 24;
        return $days . " days, " . $hours . " hours";
    }
    if (($use_secs == false) && ($hours > 0)) {
        $mins -= $hours * 60;
        return $hours . " hours, " . $mins . " mins";
    }
    if ($use_secs == false) {
        return $mins . " mins";
    }
    $dif -= $mins * 60;
    if ($mins > 0) {
        return $mins . " mins, " . $dif . " secs";
    }
    return $dif . " secs";
}
function is_checked(bool $input_value): string
{
    if ($input_value == true) {
        return " checked ";
    }
    return "";
}
