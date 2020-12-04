<?php

if (getenv('DB_HOST') !== false) {
    include "../App/Config/default.db.php";
} else {
    if (file_exists("../App/Config/db_installed.php") == true) {
        include "../App/Config/db_installed.php";
    }
}
