<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

class Db extends ErrorLogging
{
    protected string $dbHost = "localhost";
    protected string $dbName = "test";
    protected string $dbUser = "root";
    protected string $dbPass = "";
    public function __construct()
    {
        if (getenv('DB_HOST') !== false) {
            // Live
            $this->addError("Switching to live config");
            $this->dbHost = getenv('DB_HOST');
            $this->dbName = getenv('DB_DATABASE');
            $this->dbUser = getenv('DB_USERNAME');
            $this->dbPass = getenv('DB_PASSWORD');
            return;
        }
        $this->addError("using default config");
    }
}
