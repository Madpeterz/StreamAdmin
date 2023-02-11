<?php

use YAPF\Core\ErrorControl\ErrorLogging;

class Db extends ErrorLogging
{
    public $dbHost = "localhost";
    public $dbName = "";
    public $dbUser = "root";
    public $dbPass = "";
    public function __construct()
    {
        global $GEN_DATABASE_HOST, $GEN_DATABASE_USERNAME, $GEN_DATABASE_PASSWORD;
        $this->dbHost = $GEN_DATABASE_HOST;
        $this->dbUser = $GEN_DATABASE_USERNAME;
        $this->dbPass = $GEN_DATABASE_PASSWORD;
        $this->addError("Using GEN db config - if you see this message on live something is very wrong");
    }
}
