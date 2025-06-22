<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

class Db extends ErrorLogging
{
    public $dbHost = "172.30.225.230";
    public ?string $dbName = "test";
    public $dbUser = "superadmin";
    public $dbPass = "superadmin";
    public function __construct() {}
}
