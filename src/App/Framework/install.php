<?php

function install_ok(): bool
{
    if (getenv('DB_HOST') !== false) {
        if (getenv('INSTALL_OK') !== false) {
            if (getenv('INSTALL_OK') == 1) {
                if (defined("INSTALLED") == false) {
                    include "../App/Flags/installedFlags.php";
                }
                return true;
            }
        }
    }
    if (file_exists("../App/Config/ready.txt") == true) {
        if (defined("INSTALLED") == false) {
            include "../App/Flags/installedFlags.php";
        }
        return true;
    }
    return false;
}
