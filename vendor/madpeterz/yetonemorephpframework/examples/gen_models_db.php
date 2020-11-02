<?php

namespace App;

class Db extends ErrorLogging
{
    protected $dbHost = GEN_DATABASE_HOST;
    public $dbName = "";
    protected $dbUser = GEN_DATABASE_USERNAME;
    protected $dbPass = GEN_DATABASE_PASSWORD;
}
