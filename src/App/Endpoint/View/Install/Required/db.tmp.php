<?php

namespace App;

use YAPF\Core\ErrorControl\ErrorLogging as ErrorLogging;

class Db extends ErrorLogging
{
    protected $dbHost = "[[DB_HOST_HERE]]";
    protected $dbName = "[[DB_NAME_HERE]]";
    protected $dbUser = "[[DB_USER_HERE]]";
    protected $dbPass = "[[DB_PASSWORD_HERE]]";
}
