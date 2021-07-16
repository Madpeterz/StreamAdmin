<?php

if (getenv('DB_HOST') !== false) {
    include ROOTFOLDER . "/App/Config/default.db.php";
    include ROOTFOLDER . "/App/Flags/DbConfigFound.php";
} else {
    if (file_exists(ROOTFOLDER . "/App/Config/db_installed.php") == true) {
        include ROOTFOLDER . "/App/Flags/DbConfigFound.php";
        include ROOTFOLDER . "/App/Config/db_installed.php";
    }
}
