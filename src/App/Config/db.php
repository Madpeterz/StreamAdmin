<?php

namespace App;

if (getenv('DB_HOST') !== false) {
    include "../shared/config/default.db.php";
} else {
    if (file_exists("../shared/config/db_installed.php") == true) {
        include "../shared/config/db_installed.php";
    }
}
