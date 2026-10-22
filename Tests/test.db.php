<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

class Db extends ErrorLogging
{
    public $dbHost = "localhost";
    public ?string $dbName = "test";
    public $dbUser = "root";
    public $dbPass = "";
    public function __construct()
    {
        // allow running the suite inside Docker against a separate database container
        if (getenv("TEST_SQL_HOST") !== false) {
            $this->dbHost = getenv("TEST_SQL_HOST");
            $this->dbUser = getenv("TEST_SQL_USER") ?: $this->dbUser;
            $this->dbPass = getenv("TEST_SQL_PASS") ?: $this->dbPass;
        }
    }
}
