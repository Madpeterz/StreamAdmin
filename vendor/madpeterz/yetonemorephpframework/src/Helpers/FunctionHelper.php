<?php

namespace YAPF\Framework\Helpers;

/*
    YAPF/Helpers/FunctionHelper.php

    functions used all over the place nice to have
    quick access to
*/

class FunctionHelper
{
    public static function sha256(string $input): string
    {
        return hash("sha256", $input, false);
    }

    public static function userAgentIdToName(int $agentId): string
    {
        $agents = [
            1 => 'Unknown',
            2 => 'Internet Explorer',
            3 => 'Mozilla Firefox',
            4 => 'Google Chrome',
            5 => 'Apple Safari',
            6 => 'Opera',
            7 => 'Netscape',
        ];
        if (array_key_exists($agentId, $agents) == false) {
            return '?';
        }
        return $agents[$agentId];
    }

    public static function strContains(string $source, string $match): bool
    {
        if (strpos($source, $match) !== false) {
            return true;
        }
        return false;
    }

    public static function timeDisplay(int $secs): string
    {
        $mins = floor($secs / 60);
        $secs -= $mins * 60;
        $hours = floor($mins / 60);
        $mins -= $hours * 60;
        $days = floor($hours / 24);
        $hours -= $days * 24;
        $output = "";
        $addon = "";
        if ($days > 0) {
            $output .= $addon . $days . " days";
            $addon = ", ";
        }
        if ($hours > 0) {
            $output .= $addon . $hours . " hours";
            $addon = ", ";
        }
        if ($mins > 0) {
            $output .= $addon . $mins . " mins";
            $addon = ", ";
        }
        if ($secs > 0) {
            $output .= $addon . $secs . " secs";
            $addon = ", ";
        }
        if ($output == "") {
            $output = "-";
        }
        return $output;
    }

    public function expiredAgo(
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
    public function getOpts(): array
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
    public function timeleftHoursAndDays($unixtime = 0, bool $use_secs = false, string $expiredWord = "Expired"): string
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
    public function isChecked(bool $input_value): string
    {
        if ($input_value == true) {
            return " checked ";
        }
        return "";
    }
}
