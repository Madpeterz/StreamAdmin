<?php

namespace App;

if (getenv('DB_HOST') !== false) {
    echo "ENV value was set<br/>";
}

echo getenv('DB_HOST') . " VS " . $_ENV["DB_HOST"];
