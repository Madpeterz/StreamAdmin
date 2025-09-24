<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

class Db extends ErrorLogging
{
    public $dbHost = "localhost";
    public ?string $dbName = "test";
    public $dbUser = "root";
    public $dbPass = "";
    public function __construct() {}
}
