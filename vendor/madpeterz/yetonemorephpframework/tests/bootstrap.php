<?php
/*
    See spec.txt
    for load order for testing
*/
error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);

include("vendor/autoload.php");

use YAPF\Core\ErrorLogging as ErrorLogging;

class ErrorLoggingTestClass extends ErrorLogging
{
    /** 
     * by default addError is protected
     */
    public function test_addError(string $fl = "", string $fn = "", string $er = "", array $ext = []): array
    {
        return $this->addError($fl,$fn,$er,$ext);
    }
}

?>
