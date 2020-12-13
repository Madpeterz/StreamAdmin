<?php

function install_ok(): bool
{
    if (getenv('DB_HOST') !== false) {
        if (getenv('INSTALL_OK') !== false) {
            if (getenv('INSTALL_OK') == 1) {
                include "../App/Framework/installed_flags.php";
                return true;
            }
        }
    }
    if (file_exists("../App/Config/ready.txt") == true) {
        include "../App/Framework/installed_flags.php";
        return true;
    }
    return false;
}
