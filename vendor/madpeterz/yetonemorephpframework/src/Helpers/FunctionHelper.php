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
}
