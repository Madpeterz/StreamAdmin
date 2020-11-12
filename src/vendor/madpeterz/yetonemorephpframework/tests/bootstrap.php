<?php

namespace YAPFtest;

/*
    See spec.txt
    for load order for testing
*/
error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);

include("vendor/autoload.php");
include("tests/test.db.php");

// load DB
use YAPF\MySQLi\MysqliEnabled as MysqliConnector;
use YAPF\Core\ErrorLogging as ErrorLogging;

class ErrorLoggingTestClass extends ErrorLogging
{
    /**
     * by default addError is protected
     */
    public function test_addError(string $fl = "", string $fn = "", string $er = "", array $ext = []): array
    {
        return $this->addError($fl, $fn, $er, $ext);
    }
}
