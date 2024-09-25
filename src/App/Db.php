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
        if (getenv('SQL_HOST') !== false) {
            // Live
            $this->addError("Switching to live config");
            $this->dbHost = getenv('SQL_HOST');
            $this->dbName = getenv('SQL_DB_NAME');
            $this->dbUser = getenv('SQL_DB_USER');
            $this->dbPass = getenv('SQL_DB_PASS');
            return;
        }
        $this->addError("using default config");
    }
}
