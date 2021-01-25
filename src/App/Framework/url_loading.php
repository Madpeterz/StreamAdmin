<?php

if (array_key_exists("REQUEST_URI", $_SERVER) == true) {
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    $bits = array_values(array_diff(explode("/", $uri_parts[0]), [""]));
    if (count($bits) > 0) {
        if (strpos($bits[0], "php") !== false) {
            array_shift($bits);
        }
    }
    if (count($bits) == 1) {
        $module = urldecode($bits[0]);
    } elseif (count($bits) >= 2) {
        if (count($bits) >= 1) {
            $module = $bits[0 ];
        }
        if (count($bits) >= 2) {
            $area = $bits[1];
        }
        if (count($bits) >= 3) {
            $page = $bits[2];
        }
        if (count($bits) >= 4) {
            $optional = $bits[3];
        }
    }
}
