<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

class Db extends ErrorLogging
{
    public $dbHost = "127.0.0.1";
    public $dbName = "test";
    public $dbUser = "testsuser";
    public $dbPass = "testsuserPW";
    public function __construct()
    {
    }
}

