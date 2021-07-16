<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

class Db extends ErrorLogging
{
    protected $dbHost = "127.0.0.1";
    protected $dbName = "test";
    protected $dbUser = "testsuser";
    protected $dbPass = "testsuserPW";
}
