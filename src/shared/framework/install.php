<?php

function install_ok()
{
    if (getenv('DB_HOST') !== false) {
        if (getenv('INSTALL_OK') !== false) {
            if (getenv('INSTALL_OK') == 1) {
                define("installed", true);
                return true;
            }
        }
    }
    if (file_exists("../shared/ready.txt") == true) {
        define("installed", true);
        return true;
    }
    return false;
}
if (install_ok() == true) {
    include "../shared/framework/load.php";
}
