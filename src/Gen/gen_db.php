<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging;

class Db extends ErrorLogging
{
    public $dbHost = "localhost";
    public $dbName = "test";
    public $dbUser = "root";
    public $dbPass = "";
    public function __construct()
    {
    }
}
