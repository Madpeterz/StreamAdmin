<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

class Db extends ErrorLogging
{
    protected string $dbHost = "127.0.0.1";
    protected string $dbName = "test";
    protected string $dbUser = "testuser";
    protected string $dbPass = "testsuserPW";
}
